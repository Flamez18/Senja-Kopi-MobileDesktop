@extends('admin.layouts.app')

@section('title', 'Kelola Stok Cabang')

@section('header_title')
    Stok Cabang: {{ $branch->name }}
@endsection

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-box-seam me-2 text-warning"></i> Daftar Ketersediaan Menu</h5>
            <p class="text-muted small m-0">Ubah status ketersediaan produk di cabang Anda secara instan jika bahan baku habis.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th>Menu Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th class="text-center">Status Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                <tr>
                    <td class="font-weight-700">{{ $stock->product->name }}</td>
                    <td><small class="text-muted">{{ Str::limit($stock->product->description, 70) }}</small></td>
                    <td class="font-weight-600">Rp {{ number_format($stock->product->price, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <form action="{{ route('admin.stocks.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $stock->product_id }}">
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            
                            @if($stock->is_available)
                                <button type="submit" class="btn btn-success px-4 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                                </button>
                            @else
                                <button type="submit" class="btn btn-danger px-4 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i> Habis
                                </button>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Belum ada menu produk terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
