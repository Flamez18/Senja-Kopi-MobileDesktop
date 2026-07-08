@extends('admin.layouts.app')

@section('title', 'Tambah Kategori Baru')

@section('header_title', 'Tambah Kategori Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-4"><i class="bi bi-plus-lg text-primary me-2"></i> Form Tambah Kategori</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label font-weight-600">Nama Kategori</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Non-Copi" required>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label font-weight-600">Urutan Tampil (Sort Order)</label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', '0') }}" required>
                    <small class="text-muted">Kategori dengan angka lebih kecil akan tampil paling kiri/pertama.</small>
                </div>

                <div class="mb-4">
                    <label for="icon" class="form-label font-weight-600">File Gambar Icon</label>
                    <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                    <small class="text-muted">Format: PNG, JPG, JPEG, SVG. Maks: 1MB</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-coffee px-4">
                        <i class="bi bi-save me-1"></i> Simpan Kategori
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
