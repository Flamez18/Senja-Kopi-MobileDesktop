@extends('admin.layouts.app')

@section('title', 'Dashboard Admin Cabang')

@section('header_title')
    {{ $branch->name }}
@endsection

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
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Pendapatan Cabang</span>
                <h3 class="m-0 font-weight-800 text-dark">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctRevenue >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $pctRevenue >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctRevenue), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-success font-weight-600"><i class="bi bi-check-all"></i> Hanya dari pesanan lunas</small>
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
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Total Order Cabang</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $totalOrders }}</h3>
                @if($period !== 'all')
                <small class="font-weight-600 {{ $pctOrders >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $pctOrders >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                    {{ number_format(abs($pctOrders), 1) }}% vs periode lalu
                </small>
                @else
                <small class="text-muted font-weight-600"><i class="bi bi-receipt"></i> Semua transaksi masuk</small>
                @endif
            </div>
            <div class="fs-1 text-primary opacity-75">
                <i class="bi bi-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Processing Orders Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Sedang Dibuat / Proses</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $processingOrders }}</h3>
                <small class="text-warning font-weight-600"><i class="bi bi-fire"></i> Perlu segera diproses</small>
            </div>
            <div class="fs-1 text-warning opacity-75">
                <i class="bi bi-hourglass-split text-warning"></i>
            </div>
        </div>
    </div>

    <!-- Ready Orders Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Siap Diambil</span>
                <h3 class="m-0 font-weight-800 text-dark">{{ $readyOrders }}</h3>
                <small class="text-success font-weight-600"><i class="bi bi-bell-fill text-success"></i> Menunggu pickup customer</small>
            </div>
            <div class="fs-1 text-success opacity-75">
                <i class="bi bi-bag-check-fill text-success"></i>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Line Chart Row -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-graph-up me-2 text-coffee" style="color: var(--primary-coffee) !important;"></i> Grafik Tren Pendapatan Harian</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Alert -->
<div class="card card-premium p-4 mb-4 bg-white">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="font-weight-700 text-dark mb-2">Halo, Admin {{ $branch->name }}!</h5>
            <p class="text-muted m-0">
                Gunakan panel ini untuk memantau pesanan masuk secara real-time, mengubah status pembuatan minuman/makanan, dan memperbarui ketersediaan stok produk di cabang Anda hari ini.
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-coffee me-2">
                <i class="bi bi-receipt me-1"></i> Proses Pesanan
            </a>
            <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline-coffee">
                <i class="bi bi-box-seam me-1"></i> Update Stok
            </a>
        </div>
    </div>
</div>

<!-- Recent Orders for Branch -->
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-bell me-2 text-primary"></i> Pesanan Terbaru Masuk</h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-coffee">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle table-hover m-0">
            <thead>
                <tr class="table-light">
                    <th>No. Order</th>
                    <th>Nama Customer</th>
                    <th>Metode Bayar</th>
                    <th>Total</th>
                    <th>Status Pembayaran</th>
                    <th>Status Pesanan</th>
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
                    <td>{{ $order->user->name }}</td>
                    <td><span class="badge bg-light text-dark text-uppercase">{{ $order->payment_method }}</span></td>
                    <td class="font-weight-600">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge-status badge-{{ $order->payment_status }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status badge-{{ $order->order_status }}">
                            {{ $order->order_status_label }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada pesanan di cabang ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
        const chartData = @json($chartData);
        
        new Chart(ctx, {
            type: 'line',
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
