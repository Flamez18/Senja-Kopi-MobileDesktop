@extends('admin.layouts.app')

@section('title', 'Kelola Pesanan')

@section('header_title', 'Kelola Pesanan Masuk')

@section('styles')
<style>
    .pagination .page-link {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem;
        padding: 6px 12px;
        transition: all 0.2s;
    }
    .pagination .page-link:hover {
        background-color: var(--accent-cream-dark) !important;
    }
    .pagination .page-item.active .page-link:hover {
        background-color: var(--primary-coffee-light) !important;
    }
    .pagination .page-item.disabled .page-link {
        opacity: 0.4;
    }
</style>
@endsection

@section('content')
<div class="card card-premium p-4 mb-4 bg-white">
    <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-funnel me-1 text-primary"></i> Filter Pencarian</h5>

    <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3">
        @if(Auth::user()->isSuperAdmin())
        <div class="col-md-3">
            <label class="form-label small font-weight-600">Cabang Pengambilan</label>
            <select name="branch_id" class="form-select">
                <option value="">Semua Cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-3">
            <label class="form-label small font-weight-600">Status Pesanan</label>
            <select name="order_status" class="form-select">
                <option value="">Semua Status</option>
                <option value="waiting_payment" {{ request('order_status') == 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Diproses</option>
                <option value="making" {{ request('order_status') == 'making' ? 'selected' : '' }}>Sedang Dibuat</option>
                <option value="ready" {{ request('order_status') == 'ready' ? 'selected' : '' }}>Siap Diambil</option>
                <option value="completed" {{ request('order_status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small font-weight-600">Status Pembayaran</label>
            <select name="payment_status" class="form-select">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Belum Lunas</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-coffee w-100 me-2">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="card card-premium p-4">
    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th>No. Order</th>
                    @if(Auth::user()->isSuperAdmin())
                        <th>Cabang</th>
                    @endif
                    <th>Customer</th>
                    <th>Metode</th>
                    <th>Total Bayar</th>
                    <th>Pembayaran</th>
                    <th>Status Pesanan</th>
                    <th>Tanggal</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="font-weight-700">{{ $order->order_number }}</td>
                    @if(Auth::user()->isSuperAdmin())
                        <td><small class="text-muted">{{ $order->branch->name }}</small></td>
                    @endif
                    <td>
                        <div class="font-weight-600">{{ $order->user->name }}</div>
                        <small class="text-muted">{{ $order->user->phone }}</small>
                    </td>
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
                    <td><small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small></td>
                    <td class="text-center">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-coffee">
                            <i class="bi bi-eye-fill"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isSuperAdmin() ? 9 : 8 }}" class="text-center text-muted py-4">
                        Belum ada pesanan yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination -->
    @if($orders->hasPages())
    <div class="mt-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <small class="text-muted">
            Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0" style="gap: 4px;">
                {{-- Previous --}}
                @if($orders->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link rounded-3 border-0 bg-light text-muted">
                            <i class="bi bi-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link rounded-3 border-0 bg-light text-dark"
                           href="{{ $orders->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Page Numbers --}}
                @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                    @if($page == $orders->currentPage())
                        <li class="page-item active">
                            <span class="page-link rounded-3 border-0 fw-bold text-white"
                                  style="background-color: var(--primary-coffee);">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link rounded-3 border-0 bg-light text-dark"
                               href="{{ $url }}&{{ http_build_query(request()->except('page')) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($orders->hasMorePages())
                    <li class="page-item">
                        <a class="page-link rounded-3 border-0 bg-light text-dark"
                           href="{{ $orders->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link rounded-3 border-0 bg-light text-muted">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
