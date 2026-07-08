@extends('admin.layouts.app')

@section('title', 'Tambah Cabang Baru')

@section('header_title', 'Tambah Cabang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-4"><i class="bi bi-plus-lg text-primary me-2"></i> Form Tambah Cabang</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.branches.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label font-weight-600">Nama Cabang</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kopi Senja – Menteng" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="city" class="form-label font-weight-600">Kota</label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" placeholder="Contoh: Jakarta Pusat" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label font-weight-600">Nomor Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 021-xxxxxxx">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="open_time" class="form-label font-weight-600">Jam Buka</label>
                        <input type="time" class="form-control" id="open_time" name="open_time" value="{{ old('open_time', '08:00') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="close_time" class="form-label font-weight-600">Jam Tutup</label>
                        <input type="time" class="form-control" id="close_time" name="close_time" value="{{ old('close_time', '22:00') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label font-weight-600">Alamat Lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Tuliskan alamat lengkap cabang..." required>{{ old('address') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label font-weight-600" for="is_active">Aktifkan Cabang Langsung</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-coffee px-4">
                        <i class="bi bi-save me-1"></i> Simpan Cabang
                    </button>
                    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
