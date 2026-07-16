<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $period = $request->input('period', 'month');
        $now = now();
        $startDate = null;
        $endDate = $now;
        $prevStartDate = null;
        $prevEndDate = null;

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $prevStartDate = now()->subDay()->startOfDay();
                $prevEndDate = now()->subDay()->endOfDay();
                break;
            case 'week':
                $startDate = now()->subDays(7)->startOfDay();
                $prevStartDate = now()->subDays(14)->startOfDay();
                $prevEndDate = now()->subDays(7)->endOfDay();
                break;
            case 'month':
                $startDate = now()->subDays(30)->startOfDay();
                $prevStartDate = now()->subDays(60)->startOfDay();
                $prevEndDate = now()->subDays(30)->endOfDay();
                break;
            case 'all':
            default:
                $period = 'all';
                break;
        }

        // Count urgent orders (active processing/making orders older than 15 minutes)
        $urgentOrdersCount = Order::whereIn('order_status', ['processing', 'making'])
            ->where('created_at', '<', now()->subMinutes(15))
            ->when(!$user->isSuperAdmin(), function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->count();

        if ($user->isSuperAdmin()) {
            // Stats queries
            $revenueQuery = Order::where('payment_status', 'paid');
            $ordersQuery = Order::query();
            $completedQuery = Order::where('order_status', 'completed');
            $pendingQuery = Order::where('payment_status', 'pending');

            // Previous stats queries
            $prevRevenueQuery = Order::where('payment_status', 'paid');
            $prevOrdersQuery = Order::query();
            $prevCompletedQuery = Order::where('order_status', 'completed');
            $prevPendingQuery = Order::where('payment_status', 'pending');

            if ($startDate) {
                $revenueQuery->whereBetween('created_at', [$startDate, $endDate]);
                $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
                $completedQuery->whereBetween('created_at', [$startDate, $endDate]);
                $pendingQuery->whereBetween('created_at', [$startDate, $endDate]);

                $prevRevenueQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
                $prevOrdersQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
                $prevCompletedQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
                $prevPendingQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
            }

            $totalRevenue = (int) $revenueQuery->sum('total');
            $totalOrders = $ordersQuery->count();
            $completedOrders = $completedQuery->count();
            $pendingOrders = $pendingQuery->count();

            // Previous calculations
            $prevRevenue = $startDate ? (int) $prevRevenueQuery->sum('total') : 0;
            $prevOrders = $startDate ? $prevOrdersQuery->count() : 0;
            $prevCompleted = $startDate ? $prevCompletedQuery->count() : 0;
            $prevPending = $startDate ? $prevPendingQuery->count() : 0;

            // Pct change
            $pctRevenue = $startDate && $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
            $pctOrders = $startDate && $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;
            $pctCompleted = $startDate && $prevCompleted > 0 ? (($completedOrders - $prevCompleted) / $prevCompleted) * 100 : 0;
            $pctPending = $startDate && $prevPending > 0 ? (($pendingOrders - $prevPending) / $prevPending) * 100 : 0;

            $branchesCount = Branch::count();
            $productsCount = Product::count();
            $customersCount = User::where('role', 'customer')->count();

            // Branch performace stats with Sum
            $branchStats = Branch::withCount(['orders' => function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            }, 'orders as paid_orders_count' => function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid');
                if ($startDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            }])->withSum(['orders as revenue' => function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid');
                if ($startDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            }], 'total')->get();

            // 5 Recent orders
            $recentOrders = Order::with(['user', 'branch'])->latest()->take(5)->get();

            // Format branch stats for Chart.js
            $chartData = [
                'labels' => $branchStats->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Pendapatan (Rp)',
                        'data' => $branchStats->map(fn($b) => (int) ($b->revenue ?? 0))->toArray(),
                        'backgroundColor' => '#D4A373',
                        'borderColor' => '#3E2723',
                        'borderWidth' => 1
                    ]
                ]
            ];

            return view('admin.dashboard.super', compact(
                'totalRevenue', 'totalOrders', 'completedOrders', 'pendingOrders',
                'prevRevenue', 'prevOrders', 'prevCompleted', 'prevPending',
                'pctRevenue', 'pctOrders', 'pctCompleted', 'pctPending',
                'branchesCount', 'productsCount', 'customersCount', 'branchStats', 'recentOrders',
                'period', 'chartData', 'urgentOrdersCount'
            ));
        } else {
            $branchId = $user->branch_id;
            $branch = Branch::find($branchId);

            // Stats queries
            $revenueQuery = Order::where('branch_id', $branchId)->where('payment_status', 'paid');
            $ordersQuery = Order::where('branch_id', $branchId);
            
            // Previous stats queries
            $prevRevenueQuery = Order::where('branch_id', $branchId)->where('payment_status', 'paid');
            $prevOrdersQuery = Order::where('branch_id', $branchId);

            if ($startDate) {
                $revenueQuery->whereBetween('created_at', [$startDate, $endDate]);
                $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
                
                $prevRevenueQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
                $prevOrdersQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
            }

            $totalRevenue = (int) $revenueQuery->sum('total');
            $totalOrders = $ordersQuery->count();

            // Real-time states
            $processingOrders = Order::where('branch_id', $branchId)->whereIn('order_status', ['processing', 'making'])->count();
            $readyOrders = Order::where('branch_id', $branchId)->where('order_status', 'ready')->count();

            // Previous calculations
            $prevRevenue = $startDate ? (int) $prevRevenueQuery->sum('total') : 0;
            $prevOrders = $startDate ? $prevOrdersQuery->count() : 0;

            // Pct change
            $pctRevenue = $startDate && $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
            $pctOrders = $startDate && $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

            // 5 Recent orders
            $recentOrders = Order::with('user')->where('branch_id', $branchId)->latest()->take(5)->get();

            // Daily stats chart (last 30 days default or dynamic based on selection)
            $dailyStatsQuery = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
                ->where('branch_id', $branchId)
                ->where('payment_status', 'paid')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc');

            if ($startDate) {
                $dailyStatsQuery->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $dailyStatsQuery->where('created_at', '>=', now()->subDays(30)->startOfDay());
            }

            $dailyStats = $dailyStatsQuery->get();

            $chartData = [
                'labels' => $dailyStats->map(fn($d) => date('d M', strtotime($d->date)))->toArray(),
                'datasets' => [
                    [
                        'label' => 'Pendapatan Harian (Rp)',
                        'data' => $dailyStats->map(fn($d) => (int) $d->revenue)->toArray(),
                        'borderColor' => '#D4A373',
                        'backgroundColor' => 'rgba(212, 163, 115, 0.1)',
                        'fill' => true,
                        'tension' => 0.3
                    ]
                ]
            ];

            return view('admin.dashboard.branch', compact(
                'branch', 'totalRevenue', 'totalOrders', 'processingOrders', 'readyOrders', 'recentOrders',
                'prevRevenue', 'prevOrders', 'pctRevenue', 'pctOrders', 'period', 'chartData', 'urgentOrdersCount'
            ));
        }
    }

    /**
     * Get HTML partial of recent orders for AJAX auto-refresh.
     */
    public function recentOrders(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            $recentOrders = Order::with(['user', 'branch'])->latest()->take(5)->get();
        } else {
            $recentOrders = Order::with('user')->where('branch_id', $user->branch_id)->latest()->take(5)->get();
        }

        return view('admin.dashboard.partials.recent_orders', compact('recentOrders'));
    }
}
