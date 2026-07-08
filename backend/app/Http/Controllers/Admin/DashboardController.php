<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Stats untuk Super Admin (Seluruh Cabang)
            $totalRevenue = (int) Order::where('payment_status', 'paid')->sum('total');
            $totalOrders = Order::count();
            $completedOrders = Order::where('order_status', 'completed')->count();
            $pendingOrders = Order::where('order_status', 'waiting_payment')->count();
            
            $branchesCount = Branch::count();
            $productsCount = Product::count();
            $customersCount = User::where('role', 'customer')->count();

            // Ringkasan order per cabang
            $branchStats = Branch::withCount(['orders', 'orders as paid_orders_count' => function ($q) {
                $q->where('payment_status', 'paid');
            }])->get();

            // 5 Pesanan terbaru
            $recentOrders = Order::with(['user', 'branch'])->latest()->take(5)->get();

            return view('admin.dashboard.super', compact(
                'totalRevenue', 'totalOrders', 'completedOrders', 'pendingOrders',
                'branchesCount', 'productsCount', 'customersCount', 'branchStats', 'recentOrders'
            ));
        } else {
            // Stats untuk Admin Cabang (Spesifik Cabangnya)
            $branchId = $user->branch_id;
            $branch = Branch::find($branchId);

            $totalRevenue = (int) Order::where('branch_id', $branchId)->where('payment_status', 'paid')->sum('total');
            $totalOrders = Order::where('branch_id', $branchId)->count();
            $processingOrders = Order::where('branch_id', $branchId)->whereIn('order_status', ['processing', 'making'])->count();
            $readyOrders = Order::where('branch_id', $branchId)->where('order_status', 'ready')->count();

            // 5 Pesanan terbaru di cabang ini
            $recentOrders = Order::with('user')->where('branch_id', $branchId)->latest()->take(5)->get();

            return view('admin.dashboard.branch', compact(
                'branch', 'totalRevenue', 'totalOrders', 'processingOrders', 'readyOrders', 'recentOrders'
            ));
        }
    }
}
