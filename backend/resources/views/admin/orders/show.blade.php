@extends('admin.layouts.app')

@section('title', 'Detail Pesanan ' . $order->order_number)

@section('header_title', 'Detail Pesanan ' . $order->order_number)

@section('content')
<div class="row g-4">
    <!-- Left Column: Details & Items -->
    <div class="col-lg-8">
        <!-- Order Summary Card -->
        <div class="card card-premium p-4 mb-4">
            <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-receipt text-primary me-2"></i> Ringkasan Pesanan</h5>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="table-light">
                            <th>Nama Menu</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="font-weight-600">{{ $item->product_name }}</td>
                            <td class="text-center">Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end font-weight-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        
                        <!-- Calculation -->
                        <tr>
                            <td colspan="3" class="text-end text-muted border-0 pb-1">Subtotal</td>
                            <td class="text-end font-weight-600 border-0 pb-1">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted border-0 py-1">Biaya Layanan</td>
                            <td class="text-end font-weight-600 border-0 py-1">Rp {{ number_format($order->service_fee, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top">
                            <td colspan="3" class="text-end font-weight-700 border-0 pt-2">Total Pembayaran</td>
                            <td class="text-end font-weight-800 text-primary border-0 pt-2 fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes -->
            @if($order->notes)
            <div class="mt-3 p-3 bg-light rounded-3">
                <h6 class="font-weight-700 text-dark mb-1"><i class="bi bi-chat-text me-1 text-warning"></i> Catatan Pesanan:</h6>
                <p class="m-0 text-muted italic">"{{ $order->notes }}"</p>
            </div>
            @endif
        </div>

        <!-- Customer & Branch Information -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-premium p-4 h-100">
                    <h6 class="font-weight-700 text-dark mb-3"><i class="bi bi-person text-success me-2"></i> Informasi Customer</h6>
                    <p class="mb-1 font-weight-600">{{ $order->user->name }}</p>
                    <p class="mb-1 text-muted small"><i class="bi bi-envelope me-2"></i> {{ $order->user->email }}</p>
                    <p class="mb-0 text-muted small"><i class="bi bi-telephone me-2"></i> {{ $order->user->phone ?? 'Tidak ada nomor HP' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-premium p-4 h-100">
                    <h6 class="font-weight-700 text-dark mb-3"><i class="bi bi-shop text-warning me-2"></i> Cabang Pengambilan</h6>
                    <p class="mb-1 font-weight-600">{{ $order->branch->name }}</p>
                    <p class="mb-0 text-muted small"><i class="bi bi-geo-alt me-2"></i> {{ $order->branch->address }}, {{ $order->branch->city }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & Actions -->
    <div class="col-lg-4">
        <!-- Status & Update Card -->
        <div class="card card-premium p-4 mb-4">
            <h5 class="font-weight-700 text-dark mb-3"><i class="bi bi-gear-fill text-danger me-2"></i> Kelola Status</h5>
            
            <div class="mb-4">
                <span class="text-muted small d-block mb-1">Status Pembayaran Saat Ini:</span>
                <span class="badge-status badge-{{ $order->payment_status }} fs-6 d-inline-block">
                    {{ strtoupper($order->payment_status) }}
                </span>
            </div>

            <div class="mb-4">
                <span class="text-muted small d-block mb-1">Status Pesanan Saat Ini:</span>
                <span class="badge-status badge-{{ $order->order_status }} fs-6 d-inline-block">
                    {{ $order->order_status === 'waiting_payment' ? 'MENUNGGU PEMBAYARAN' : ($order->order_status === 'processing' ? 'DIPROSES' : ($order->order_status === 'making' ? 'SEDANG DIBUAT' : ($order->order_status === 'ready' ? 'SIAP DIAMBIL' : ($order->order_status === 'completed' ? 'SELESAI' : 'DIBATALKAN')))) }}
                </span>
            </div>

            <hr>

            <!-- Form Update Status -->
            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small font-weight-600">Update Status Pembayaran</label>
                    <select name="payment_status" class="form-select">
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="expired" {{ $order->payment_status === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small font-weight-600">Update Status Pesanan</label>
                    <select name="order_status" class="form-select">
                        <option value="waiting_payment" {{ $order->order_status === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Diproses</option>
                        <option value="making" {{ $order->order_status === 'making' ? 'selected' : '' }}>Sedang Dibuat</option>
                        <option value="ready" {{ $order->order_status === 'ready' ? 'selected' : '' }}>Siap Diambil</option>
                        <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-coffee w-100">
                    <i class="bi bi-save me-1"></i> Perbarui Status
                </button>
            </form>
        </div>

        <!-- Order Info Metadata -->
        <div class="card card-premium p-4">
            <h6 class="font-weight-700 text-dark mb-3"><i class="bi bi-info-circle me-2 text-info"></i> Metadata Transaksi</h6>
            <div class="small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Metode:</span>
                    <span class="font-weight-600 text-uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">ID Transaksi Midtrans:</span>
                    <span class="font-weight-600 text-muted">{{ $order->midtrans_transaction_id ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Waktu Order:</span>
                    <span class="font-weight-600">{{ $order->created_at->format('d M Y H:i:s') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Waktu Lunas:</span>
                    <span class="font-weight-600 text-success">{{ $order->paid_at ? $order->paid_at->format('d M Y H:i:s') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
