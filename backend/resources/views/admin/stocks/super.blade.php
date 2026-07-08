@extends('admin.layouts.app')

@section('title', 'Kelola Stok Seluruh Cabang')

@section('header_title', 'Kelola Stok Seluruh Cabang')

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-box-seam me-2 text-warning"></i> Grid Ketersediaan Stok</h5>
            <p class="text-muted small m-0">Super admin dapat menonaktifkan ketersediaan menu tertentu di cabang manapun secara instan.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th>Menu Produk</th>
                    <th>Kategori</th>
                    @foreach($branches as $branch)
                        <th class="text-center">{{ $branch->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="font-weight-700">{{ $product->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $product->category->name }}</span></td>
                    @foreach($branches as $branch)
                        @php
                            $isAvailable = $stocks["{$product->id}-{$branch->id}"] ?? false;
                        @endphp
                        <td class="text-center">
                            <form action="{{ route('admin.stocks.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                
                                @if($isAvailable)
                                    <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-danger px-3 rounded-pill">
                                        <i class="bi bi-x-circle-fill me-1"></i> Habis
                                    </button>
                                @endif
                            </form>
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 2 + count($branches) }}" class="text-center text-muted py-4">Belum ada menu produk terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
