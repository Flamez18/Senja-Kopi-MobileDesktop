@extends('admin.layouts.app')

@section('title', 'Kelola Kategori')

@section('header_title', 'Kelola Kategori')

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-grid-fill me-2 text-warning"></i> Daftar Kategori Menu</h5>
            <p class="text-muted small m-0">Mengatur kelompok menu kopi, minuman non-kopi, makanan, dan urutan tampilannya.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-coffee">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th style="width: 100px;">Icon</th>
                    <th>Nama Kategori</th>
                    <th>Urutan Tampil (Sort)</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>
                        @if($category->icon_url)
                            <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" class="p-2 bg-light rounded-3" style="width: 50px; height: 50px; border: 1px solid var(--accent-cream-dark); object-fit: contain;">
                        @else
                            <div class="bg-light text-muted rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-tag fs-4"></i>
                            </div>
                        @endif
                    </td>
                    <td class="font-weight-700 text-dark">{{ $category->name }}</td>
                    <td class="font-weight-600"><span class="badge bg-secondary">{{ $category->sort_order }}</span></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-coffee">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Semua menu produk di dalam kategori ini juga akan terhapus secara permanen.')">
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
                    <td colspan="4" class="text-center text-muted py-4">Belum ada kategori terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
