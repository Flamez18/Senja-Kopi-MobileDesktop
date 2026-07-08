<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
            'order_status' => ['required', 'in:waiting_payment,processing,making,ready,completed,cancelled'],
            'payment_status' => ['required', 'in:pending,paid,failed,expired'],
        ]);

        $user = Auth::user();
        $query = Order::query();

        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->findOrFail($id);

        $order->update([
            'order_status' => $request->order_status,
            'payment_status' => $request->payment_status,
            'paid_at' => ($request->payment_status === 'paid' && !$order->paid_at) ? now() : $order->paid_at,
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
