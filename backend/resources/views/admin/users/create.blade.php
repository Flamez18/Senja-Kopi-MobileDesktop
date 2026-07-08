@extends('admin.layouts.app')

@section('title', 'Tambah Admin Cabang')

@section('header_title', 'Tambah Admin Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium p-4">
            <h5 class="font-weight-700 text-dark mb-4"><i class="bi bi-person-plus text-primary me-2"></i> Form Registrasi Staf</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label font-weight-600">Nama Lengkap Staf</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kenlie Athalla" required>
                    </div>
                    <div class="col-md-6">
                        <label for="branch_id" class="form-label font-weight-600">Cabang Penugasan</label>
                        <select id="branch_id" name="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang Fisik</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label font-weight-600">Alamat Email Login</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: staf.senopati@kopisenja.com" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label font-weight-600">Nomor HP</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 0812xxxxxxxx">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label font-weight-600">Kata Sandi Awal</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan minimal 8 karakter" required>
                    <small class="text-muted">Berikan kata sandi sementara ini pada staf terkait untuk login pertama kali.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-coffee px-4">
                        <i class="bi bi-save me-1"></i> Simpan Data Staf
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
