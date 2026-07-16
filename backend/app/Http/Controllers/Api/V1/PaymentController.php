<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Re-initiate payment to get a new Snap Token (if the old one expired or was empty).
     */
    public function initiatePayment(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        if (!in_array($order->payment_method, ['qris', 'ewallet', 'transfer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak menggunakan metode pembayaran digital.',
            ], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar.',
            ], 422);
        }

        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibatalkan.',
            ], 422);
        }

        // Jika sudah ada snap token, kembalikan saja yang sudah ada
        if ($order->midtrans_snap_token) {
            return response()->json([
                'success' => true,
                'message' => 'Token pembayaran berhasil diambil',
                'data' => [
                    'midtrans_snap_token' => $order->midtrans_snap_token
                ]
            ]);
        }

        // Jika belum ada, panggil helper di OrderController untuk generate
        // Untuk menghindari duplikasi, kita bisa memanggil langsung di sini
        $orderController = new OrderController();
        $user = $request->user();
        
        $items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Gunakan reflection atau jadikan method getMidtransSnapToken public/helper.
        // Karena kita di class yang sama, mari kita implementasi ulang fungsi helper pembuat token di sini atau buat method helper baru.
        // Agar rapi, kita tulis kodenya di sini.
        $snapToken = $this->requestSnapToken($order, $user, $items, $order->service_fee, $order->payment_method);

        if ($snapToken) {
            $order->update(['midtrans_snap_token' => $snapToken]);
            return response()->json([
                'success' => true,
                'message' => 'Token pembayaran berhasil dibuat',
                'data' => [
                    'midtrans_snap_token' => $snapToken
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat token pembayaran Midtrans.',
        ], 500);
    }

    /**
     * Handle Midtrans Webhook Notification.
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Midtrans Notification Payload: ', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return response()->json([
                'success' => false,
                'message' => 'Payload tidak lengkap'
            ], 400);
        }

        // 1. Verifikasi Signature Key
        // Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $localSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($localSignature !== $signatureKey) {
            Log::warning('Midtrans Webhook: Invalid Signature. Local: ' . $localSignature . ' | Remote: ' . $signatureKey);
            return response()->json([
                'success' => false,
                'message' => 'Signature tidak valid'
            ], 403);
        }

        // 2. Cari Order di Database
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            Log::warning('Midtrans Webhook: Order ' . $orderId . ' tidak ditemukan.');
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        // 3. Tentukan status baru berdasarkan status transaksi Midtrans
        $paymentStatus = 'pending';
        $orderStatus = $order->order_status;
        $paidAt = null;

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            $paymentStatus = 'paid';
            $orderStatus = 'processing'; // Langsung diproses setelah dibayar
            $paidAt = now();
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = 'pending';
            $orderStatus = 'waiting_payment';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $paymentStatus = ($transactionStatus === 'expire') ? 'expired' : 'failed';
            $orderStatus = 'cancelled'; // Pesanan dibatalkan jika pembayaran gagal/kedaluwarsa
        }

        // 4. Update Order
        $order->update([
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'paid_at' => $paidAt ?? $order->paid_at,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
        ]);

        if ($paymentStatus === 'paid') {
            $this->sendPaymentNotification($order);
        }

        Log::info("Order {$orderId} updated via Midtrans Webhook. Payment: {$paymentStatus}, Order: {$orderStatus}");

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil diproses'
        ]);
    }

    /**
     * Get payment status of an order (for manual checking/polling from mobile).
     */
    public function getPaymentStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        // Jika sudah paid, langsung kembalikan status dari DB
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diambil',
                'data'    => new \App\Http\Resources\OrderResource($order),
            ]);
        }

        // Untuk pesanan digital yg masih pending, cek langsung ke Midtrans
        if (in_array($order->payment_method, ['qris', 'ewallet', 'transfer']) && $order->payment_status === 'pending') {
            $serverKey    = config('midtrans.server_key');
            $isProduction = filter_var(config('midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);
            $baseUrl      = $isProduction
                ? "https://api.midtrans.com/v2/{$order->order_number}/status"
                : "https://api.sandbox.midtrans.com/v2/{$order->order_number}/status";

            try {
                $response = Http::withoutVerifying()
                    ->withBasicAuth($serverKey, '')
                    ->get($baseUrl);

                if ($response->successful()) {
                    $transactionStatus = $response->json('transaction_status');
                    $fraudStatus       = $response->json('fraud_status');

                    Log::info("Midtrans status check for {$order->order_number}: {$transactionStatus}");

                    if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                        $order->update([
                            'payment_status'         => 'paid',
                            'order_status'           => 'processing',
                            'paid_at'                => now(),
                            'midtrans_transaction_id' => $response->json('transaction_id'),
                        ]);
                        $this->sendPaymentNotification($order);
                    } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                        $order->update([
                            'payment_status' => $transactionStatus === 'expire' ? 'expired' : 'failed',
                            'order_status'   => 'cancelled',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Midtrans status check failed: ' . $e->getMessage());
            }

            $order->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diambil',
            'data'    => new \App\Http\Resources\OrderResource($order),
        ]);
    }

    /**
     * Helper to request Snap Token.
     */
    private function requestSnapToken(Order $order, $user, array $items, int $serviceFee, string $paymentMethod = 'qris'): ?string
    {
        $serverKey    = config('midtrans.server_key');
        $isProduction = filter_var(config('midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);

        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id'       => (string) $item['product_id'],
                'price'    => (int) $item['product_price'],
                'quantity' => (int) $item['quantity'],
                'name'     => substr($item['product_name'], 0, 50),
            ];
        }

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
            ],
            'callbacks' => [
                'finish' => 'kopisenja://payment/finish'
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

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->withBasicAuth($serverKey, '')
            ->post($baseUrl, $payload);

            if ($response->successful()) {
                return $response->json('redirect_url');
            }

            Log::error('Midtrans Snap Re-initiate Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Connection Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send FCM notification to customer about successful payment.
     */
    private function sendPaymentNotification(Order $order): void
    {
        $order->load('user');
        $customerToken = $order->user?->fcm_token;
        if (!$customerToken) {
            return;
        }

        try {
            $fcm = new FcmService();
            $fcm->sendToToken(
                $customerToken,
                '💰 Pembayaran Dikonfirmasi',
                "Pembayaran pesanan #{$order->order_number} berhasil. Pesanan Anda sedang diproses!",
                [
                    'order_id'     => (string) $order->id,
                    'order_number' => $order->order_number,
                    'type'         => 'payment_status',
                ]
            );
        } catch (\Exception $e) {
            Log::error('FCM payment notification failed: ' . $e->getMessage());
        }
    }
}
