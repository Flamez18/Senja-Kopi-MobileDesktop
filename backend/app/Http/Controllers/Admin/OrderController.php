<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * List orders.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Order::with(['user', 'branch'])->latest();

        // Filter jika Admin Cabang, batasi hanya cabangnya saja
        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        } else {
            // Super Admin bisa filter berdasarkan cabang
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        // Filter Status Pesanan
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(10)->withQueryString();

        // Ambil data cabang untuk dropdown filter Super Admin
        $branches = [];
        if ($user->isSuperAdmin()) {
            $branches = \App\Models\Branch::all();
        }

        return view('admin.orders.index', compact('orders', 'branches'));
    }

    /**
     * Poll pesanan baru untuk notifikasi browser admin panel.
     */
    public function pollRecent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $since = $request->query('since');

        $query = Order::with(['user', 'branch'])
            ->when($user->isAdminBranch(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest();

        if ($since) {
            $query->where('created_at', '>', $since);
        } else {
            $query->where('created_at', '>', now()->subSeconds(30));
        }

        $orders = $query->limit(10)->get()->map(fn (Order $order) => [
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'customer'     => $order->user?->name ?? 'Customer',
            'branch'       => $order->branch?->name ?? '-',
            'total'        => (int) $order->total,
            'created_at'   => $order->created_at?->toIso8601String(),
        ])->values();

        return response()->json([
            'success' => true,
            'orders'  => $orders,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Show order details.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $query = Order::with(['user', 'branch', 'items']);

        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update status of an order.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'order_status'  => ['required', 'in:waiting_payment,processing,making,ready,completed,cancelled'],
            'payment_status' => ['required', 'in:pending,paid,failed,expired'],
        ]);

        $user = Auth::user();
        $query = Order::query();

        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->with('user')->findOrFail($id);

        $order->update([
            'order_status'  => $request->order_status,
            'payment_status' => $request->payment_status,
            'paid_at'       => ($request->payment_status === 'paid' && !$order->paid_at) ? now() : $order->paid_at,
        ]);

        // Kirim notifikasi push ke customer
        $this->sendStatusNotification($order, $request->order_status);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Kirim notifikasi FCM ke customer berdasarkan status baru.
     */
    private function sendStatusNotification(Order $order, string $newStatus): void
    {
        $customerToken = $order->user?->fcm_token;
        if (!$customerToken) {
            return;
        }

        $messages = [
            'processing' => [
                'title' => '☕ Pesanan Diproses',
                'body'  => "Pesanan #{$order->order_number} sedang dibuat oleh barista kami.",
            ],
            'making' => [
                'title' => '☕ Pesanan Sedang Dibuat',
                'body'  => "Pesanan #{$order->order_number} sedang dalam proses pembuatan.",
            ],
            'ready' => [
                'title' => '🎉 Pesanan Siap!',
                'body'  => "Pesanan #{$order->order_number} sudah siap diambil. Silakan ke kasir!",
            ],
            'completed' => [
                'title' => '✅ Pesanan Selesai',
                'body'  => "Pesanan #{$order->order_number} telah selesai. Terima kasih telah mengunjungi Kopi Senja!",
            ],
            'cancelled' => [
                'title' => '❌ Pesanan Dibatalkan',
                'body'  => "Pesanan #{$order->order_number} telah dibatalkan.",
            ],
        ];

        if (!isset($messages[$newStatus])) {
            return;
        }

        $msg = $messages[$newStatus];
        $fcm = new FcmService();
        $fcm->sendToToken($customerToken, $msg['title'], $msg['body'], [
            'order_id'     => (string) $order->id,
            'order_number' => $order->order_number,
            'type'         => 'order_status',
        ]);
    }
}
