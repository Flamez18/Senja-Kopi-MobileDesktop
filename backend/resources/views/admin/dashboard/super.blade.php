@extends('admin.layouts.app')

@section('title', 'Dashboard Super Admin')

@section('header_title', 'Dashboard Ringkasan Kafe')

@section('content')
<div class="row g-4 mb-4">
    <!-- Revenue Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-premium p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <span class="text-muted text-uppercase font-weight-600 small d-block mb-1">Total Pendapatan</span>
                <h3 class="m-0 font-weight-800 text-dark">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                <small class="text-success font-weight-600"><i class="bi bi-check-all"></i> Pembayaran QRIS & Tunai</small>
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
                <small class="text-muted font-weight-600"><i class="bi bi-receipt"></i> Semua status pesanan</small>
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
                <small class="text-success font-weight-600"><i class="bi bi-patch-check-fill"></i> Siap & Telah diambil</small>
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
                <small class="text-danger font-weight-600"><i class="bi bi-clock-history"></i> Belum lunas QRIS</small>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchStats as $stat)
                        <tr>
                            <td class="font-weight-600">{{ $stat->name }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $stat->orders_count }}</span></td>
                            <td class="text-center"><span class="badge bg-success">{{ $stat->paid_orders_count }}</span></td>
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
                    <tbody>
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
                                    {{ $order->order_status === 'waiting_payment' ? 'Menunggu Bayar' : ($order->order_status === 'processing' ? 'Diproses' : ($order->order_status === 'making' ? 'Dibuat' : ($order->order_status === 'ready' ? 'Siap Ambil' : ($order->order_status === 'completed' ? 'Selesai' : 'Batal')))) }}
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
@endsection
