@extends('admin.layouts.app')

@section('title', 'Edit Kategori')

@section('header_title', 'Edit Kategori: ' . $category->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-4"><i class="bi bi-pencil-square text-primary me-2"></i> Form Edit Kategori</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label font-weight-600">Nama Kategori</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" placeholder="Contoh: Non-Copi" required>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label font-weight-600">Urutan Tampil (Sort Order)</label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" required>
                    <small class="text-muted">Kategori dengan angka lebih kecil akan tampil paling kiri/pertama.</small>
                </div>

                <div class="mb-3">
                    <label for="icon" class="form-label font-weight-600">Ganti Gambar Icon</label>
                    <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah icon.</small>
                </div>

                @if($category->icon_url)
                <div class="mb-4">
                    <label class="form-label font-weight-600 d-block">Icon Saat Ini</label>
                    <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" class="p-2 bg-light rounded-3 border" style="width: 80px; height: 80px; object-fit: contain;">
                </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-coffee px-4">
                        <i class="bi bi-save me-1"></i> Perbarui Kategori
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary px-4">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
