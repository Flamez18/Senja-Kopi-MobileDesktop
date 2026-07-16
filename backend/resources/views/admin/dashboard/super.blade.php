@extends('admin.layouts.app')

@section('title', 'Dashboard Super Admin')

@section('header_title', 'Dashboard Ringkasan Kafe')

@section('content')
<!-- Filter & Urgent Banner Row -->
<div class="d-flex flex-column gap-3 mb-4">
    @if($urgentOrdersCount > 0)
    <div class="alert alert-danger d-flex align-items-center justify-content-between border-0 shadow-sm rounded-3 m-0" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            <div>
                <strong class="d-block">Perhatian! Ada {{ $urgentOrdersCount }} pesanan yang mendesak!</strong>
                <span class="small text-danger-emphasis">Pesanan berstatus Diproses/Dibuat selama lebih dari 15 menit.</span>
            </div>
        </div>
        <a href="{{ route('admin.orders.index') }}?order_status=processing" class="btn btn-sm btn-danger rounded-3 px-3">Proses Sekarang</a>
    </div>
    @endif

    <div class="d-flex align-items-center justify-content-between bg-white p-3 border rounded-3 shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-muted fs-5"></i>
            <span class="font-weight-600 text-muted">Periode Filter:</span>
            <span class="badge text-white text-uppercase font-weight-700 px-2 py-1" style="background-color: var(--primary-coffee) !important;">
                {{ $period === 'today' ? 'Hari Ini' : ($period === 'week' ? '7 Hari Terakhir' : ($period === 'month' ? '30 Hari Terakhir' : 'Semua')) }}
            </span>
        </div>
        <form action="{{ route('admin.dashboard') }}" method="GET" id="filter-form" class="d-flex align-items-center gap-2">
            <select name="period" class="form-select" style="width: auto;" onchange="document.getElementById('filter-form').submit();">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>7 Hari Terakhir</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>30 Hari Terakhir</option>
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua Periode</option>
            </select>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Total Pendapatan</span>
                <h3 class="m-0 font-weight-800 text-dark">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctRevenue >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $pctRevenue >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctRevenue), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-success font-weight-600"><i class="bi bi-check-all"></i> Pembayaran QRIS & Tunai</small>
                @endif
            </div>
            <div class="fs-1 text-success opacity-75">
                <i class="bi bi-cash-coin"></i>
            </div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Total Transaksi</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $totalOrders }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctOrders >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $pctOrders >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctOrders), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-muted font-weight-600"><i class="bi bi-receipt"></i> Semua status pesanan</small>
                @endif
            </div>
            <div class="fs-1 text-primary opacity-75">
                <i class="bi bi-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Completed Orders Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Pesanan Selesai</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $completedOrders }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctCompleted >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $pctCompleted >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctCompleted), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-success font-weight-600"><i class="bi bi-patch-check-fill"></i> Siap & Telah diambil</small>
                @endif
            </div>
            <div class="fs-1 text-success opacity-75">
                <i class="bi bi-bag-check-fill"></i>
            </div>
        </div>
    </div>

    <!-- Pending Orders Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Menunggu Bayar</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $pendingOrders }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctPending >= 0 ? 'text-danger' : 'text-success' }}">
                    <i class="bi {{ $pctPending >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctPending), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-danger font-weight-600"><i class="bi bi-clock-history"></i> Belum lunas QRIS</small>
                @endif
            </div>
            <div class="fs-1 text-danger opacity-75">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick System Info -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-premium p-3 d-flex align-items-center gap-3 flex-row bg-white">
            <div class="p-3 bg-light text-primary rounded-3 fs-4"><i class="bi bi-shop"></i></div>
            <div>
                <h5 class="m-0 font-weight-700">{{ $branchesCount }}</h5>
                <small class="text-muted">Cabang Terdaftar</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium p-3 d-flex align-items-center gap-3 flex-row bg-white">
            <div class="p-3 bg-light text-success rounded-3 fs-4"><i class="bi bi-cup-straw"></i></div>
            <div>
                <h5 class="m-0 font-weight-700">{{ $productsCount }}</h5>
                <small class="text-muted">Menu Produk</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium p-3 d-flex align-items-center gap-3 flex-row bg-white">
            <div class="p-3 bg-light text-warning rounded-3 fs-4"><i class="bi bi-people"></i></div>
            <div>
                <h5 class="m-0 font-weight-700">{{ $customersCount }}</h5>
                <small class="text-muted">Customer Terdaftar</small>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart Row -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-bar-chart-fill me-2 text-coffee" style="color: var(--primary-coffee) !important;"></i> Grafik Pendapatan Per Cabang</h5>
            <div style="height: 320px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Branch Overview -->
    <div class="col-lg-6">
        <div class="card card-premium p-4 h-100">
            <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-shop-window me-2 text-warning"></i> Performa Cabang</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover m-0">
                    <thead>
                        <tr class="table-light">
                            <th>Nama Cabang</th>
                            <th class="text-center">Total Order</th>
                            <th class="text-center">Lunas (Paid)</th>
                            <th class="text-end">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchStats as $stat)
                        <tr>
                            <td class="font-weight-600">{{ $stat->name }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $stat->orders_count }}</span></td>
                            <td class="text-center"><span class="badge bg-success">{{ $stat->paid_orders_count }}</span></td>
                            <td class="text-end font-weight-700 text-dark">Rp {{ number_format($stat->revenue ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-6">
        <div class="card card-premium p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-clock-history me-2 text-primary"></i> Pesanan Terbaru</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-coffee">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover m-0">
                    <thead>
                        <tr class="table-light">
                            <th>No. Order</th>
                            <th>Cabang</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-table-body">
                        @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-weight-700 text-decoration-none text-dark">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td><span class="small text-muted">{{ $order->branch->name }}</span></td>
                            <td class="font-weight-600">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                             <td>
                                <span class="badge-status badge-{{ $order->order_status }}">
                                    {{ $order->order_status_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada pesanan masuk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const chartData = @json($chartData);
        
        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + Number(value).toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Auto refresh recent orders table every 15 seconds
        const pollUrl = "{{ route('admin.dashboard.recentOrders') }}";
        const tableBody = document.getElementById('recent-orders-table-body');
        
        if (tableBody) {
            setInterval(async () => {
                try {
                    const response = await fetch(pollUrl, {
                        headers: { 'Accept': 'text/html' }
                    });
                    if (response.ok) {
                        const html = await response.text();
                        tableBody.innerHTML = html;
                    }
                } catch (error) {
                    console.warn('Gagal memuat ulang pesanan terbaru:', error);
                }
            }, 15000);
        }
    });
</script>
@endsection
@endsection
