@extends('admin.layouts.app')

@section('title', 'Tambah Menu Baru')

@section('header_title', 'Tambah Menu Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-4"><i class="bi bi-plus-lg text-primary me-2"></i> Form Input Produk</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label font-weight-600">Nama Menu</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Espresso Macchiato" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label font-weight-600">Kategori</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label font-weight-600">Harga Jual (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" placeholder="Contoh: 30000" required>
                    </div>
                    <div class="col-md-6">
                        <label for="image" class="form-label font-weight-600">Foto Menu</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, WEBP. Maks: 2MB</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label font-weight-600">Deskripsi Menu</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Tuliskan deskripsi lengkap mengenai menu kopi atau makanan ini...">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_available" name="is_available" value="1" checked>
                        <label class="form-check-label font-weight-600" for="is_available">Tersedia secara global</label>
                    </div>
                    <small class="text-muted">Jika dinonaktifkan, menu ini tidak akan tampil di semua cabang.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-coffee px-4">
                        <i class="bi bi-save me-1"></i> Simpan Menu
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary px-4">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
