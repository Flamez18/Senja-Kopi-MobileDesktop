<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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

        if ($order->payment_method !== 'qris') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak menggunakan metode pembayaran QRIS.',
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
        $snapToken = $this->requestSnapToken($order, $user, $items, $order->service_fee);

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

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diambil',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
            ]
        ]);
    }

    /**
     * Helper to request Snap Token (copied to avoid tight controller coupling).
     */
    private function requestSnapToken(Order $order, $user, array $items, int $serviceFee): ?string
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        
        $baseUrl = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => (string) $item['product_id'],
                'price' => (int) $item['product_price'],
                'quantity' => (int) $item['quantity'],
                'name' => substr($item['product_name'], 0, 50),
            ];
        }

        if ($serviceFee > 0) {
            $itemDetails[] = [
                'id' => 'service_fee',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'enabled_payments' => ['gopay', 'shopeepay', 'qris', 'other_qris'],
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->withBasicAuth($serverKey, '')
            ->post($baseUrl, $payload);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Midtrans Snap Re-initiate Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Connection Exception: ' . $e->getMessage());
            return null;
        }
    }
}
