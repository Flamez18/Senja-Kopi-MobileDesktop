<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Create a new order (Checkout).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id'              => ['required', 'exists:branches,id'],
            'payment_method'         => ['required', 'in:cash,qris,ewallet,transfer'],
            'notes'                  => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $branchId = $request->branch_id;
        $paymentMethod = $request->payment_method;
        
        // 1. Validasi Cabang Aktif
        $branch = Branch::where('is_active', true)->find($branchId);
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang yang dipilih sedang tidak aktif.',
            ], 422);
        }

        // Mulai database transaction agar aman
        return DB::transaction(function () use ($request, $user, $branch, $paymentMethod) {
            $subtotal = 0;
            $orderItemsData = [];

            // 2. Validasi Stok dan Hitung Subtotal
            foreach ($request->items as $item) {
                $product = Product::with(['stocks' => function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                }])->find($item['product_id']);

                // Cek ketersediaan produk secara global
                if (!$product->is_available) {
                    return response()->json([
                        'success' => false,
                        'message' => "Produk {$product->name} sedang tidak tersedia.",
                    ], 422);
                }

                // Cek ketersediaan stok di cabang
                $stock = $product->stocks->first();
                if (!$stock || !$stock->is_available) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok produk {$product->name} habis di cabang {$branch->name}.",
                    ], 422);
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            // 3. Hitung Biaya Layanan & Total
            $serviceFee = (int) env('SERVICE_FEE', 2000);
            $total = $subtotal + $serviceFee;

            // 4. Generate Order Number unik (CS-XXXXX)
            do {
                $randomNumber = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
                $orderNumber = 'CS-' . $randomNumber;
            } while (Order::where('order_number', $orderNumber)->exists());

            // 5. Tentukan status awal berdasarkan metode pembayaran
            $orderStatus = 'waiting_payment';
            $paymentStatus = 'pending';

            if ($paymentMethod === 'cash') {
                // Sesuai request: Cash langsung paid dan processing
                $orderStatus = 'processing';
                $paymentStatus = 'paid';
            }

            // 6. Buat Instance Order
            $order = Order::create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'order_number' => $orderNumber,
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $total,
                'paid_at' => $paymentMethod === 'cash' ? now() : null,
            ]);

            // 7. Simpan detail item pesanan
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // 8. Jika menggunakan QRIS / E-Wallet / Transfer (Midtrans), generate Snap Token
            if (in_array($paymentMethod, ['qris', 'ewallet', 'transfer'])) {
                $snapToken = $this->getMidtransSnapToken($order, $user, $orderItemsData, $serviceFee, $paymentMethod);
                if ($snapToken) {
                    $order->update([
                        'midtrans_snap_token' => $snapToken
                    ]);
                } else {
                    // Jika gagal generate token dari Midtrans, throw exception agar rollback
                    throw new \Exception('Gagal menginisialisasi pembayaran dengan Midtrans. Silakan coba lagi.');
                }
            }

            $order->load(['branch', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => new OrderResource($order)
            ], 201);
        });
    }

    /**
     * Get order history of authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['branch', 'items'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan berhasil diambil',
            'data' => OrderResource::collection($orders)
        ]);
    }

    /**
     * Get detail of a single order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with(['branch', 'items'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Cancel an order.
     * Only allowed if order is in 'waiting_payment' status.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        if ($order->order_status !== 'waiting_payment') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena sedang diproses atau sudah selesai.',
            ], 422);
        }

        $order->update([
            'order_status' => 'cancelled',
            'payment_status' => 'failed' // Update payment status as failed/cancelled
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan',
            'data' => new OrderResource($order->load(['branch', 'items']))
        ]);
    }

    /**
     * Request Snap Token from Midtrans.
     */
    private function getMidtransSnapToken(Order $order, $user, array $items, int $serviceFee, string $paymentMethod = 'qris'): ?string
    {
        $serverKey   = config('midtrans.server_key');
        $isProduction = filter_var(config('midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);

        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // Susun item_details untuk Midtrans
        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id'       => (string) $item['product_id'],
                'price'    => (int) $item['product_price'],
                'quantity' => (int) $item['quantity'],
                'name'     => substr($item['product_name'], 0, 50),
            ];
        }

        // Tambahkan biaya layanan ke item details
        if ($serviceFee > 0) {
            $itemDetails[] = [
                'id'       => 'service_fee',
                'price'    => $serviceFee,
                'quantity' => 1,
                'name'     => 'Biaya Layanan',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'item_details'     => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'credit_card' => [
                'secure' => true,
            ]
        ];

        // Limit enabled payments based on selected payment method
        if ($paymentMethod === 'qris') {
            $payload['enabled_payments'] = ['qris', 'other_qris', 'gopay', 'shopeepay'];
        } elseif ($paymentMethod === 'ewallet') {
            $payload['enabled_payments'] = ['gopay', 'shopeepay'];
        } elseif ($paymentMethod === 'transfer') {
            $payload['enabled_payments'] = ['bank_transfer'];
        }

        Log::info('Midtrans Server Key Used: ' . substr($serverKey, 0, 20) . '...');
        Log::info('Midtrans Payload: ', ['order_number' => $order->order_number, 'total' => $order->total]);

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->withBasicAuth($serverKey, '')
            ->post($baseUrl, $payload);

            if ($response->successful()) {
                Log::info('Midtrans Snap Redirection URL generated: ' . $response->json('redirect_url'));
                return $response->json('redirect_url');
            }

            Log::error('Midtrans Snap Error Response: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Connection Exception: ' . $e->getMessage());
            return null;
        }
    }
}
