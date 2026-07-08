@extends('admin.layouts.app')

@section('title', 'Kelola Menu Produk')

@section('header_title', 'Kelola Menu Produk')

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-cup-straw me-2 text-warning"></i> Daftar Menu</h5>
            <p class="text-muted small m-0">Tambah, edit, atau hapus menu kopi, non-kopi, dan makanan kafe Kopi Senja.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-coffee">
            <i class="bi bi-plus-lg me-1"></i> Tambah Menu Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Foto</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status Ketersediaan Global</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3 object-fit-cover" style="width: 60px; height: 60px; border: 1px solid var(--accent-cream-dark);">
                        @else
                            <div class="bg-light text-muted rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-image fs-4"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="font-weight-700 text-dark">{{ $product->name }}</div>
                        <small class="text-muted d-block" style="max-width: 300px;">{{ Str::limit($product->description, 60) }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $product->category->name }}</span></td>
                    <td class="font-weight-600">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->is_available)
                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-coffee">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini? Semua data stok cabang terkait juga akan dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada menu terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
