@extends('admin.layouts.app')

@section('title', 'Kelola Admin Cabang')

@section('header_title', 'Kelola Admin Cabang')

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-people-fill me-2 text-warning"></i> Akun Admin Cabang</h5>
            <p class="text-muted small m-0">Daftar staf admin yang ditugaskan mengelola pesanan dan stok di masing-masing cabang fisik.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-coffee">
            <i class="bi bi-plus-lg me-1"></i> Tambah Admin Cabang
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Cabang Penugasan</th>
                    <th>Tanggal Terdaftar</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td class="font-weight-700 text-dark">{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>{{ $admin->phone ?? '-' }}</td>
                    <td>
                        @if($admin->branch)
                            <span class="badge bg-light text-dark font-weight-600">{{ $admin->branch->name }}</span>
                        @else
                            <span class="badge bg-danger">Tidak ditugaskan</span>
                        @endif
                    </td>
                    <td><small class="text-muted">{{ $admin->created_at->format('d M Y, H:i') }}</small></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.users.edit', $admin->id) }}" class="btn btn-sm btn-outline-coffee">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin cabang ini?')">
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
                    <td colspan="6" class="text-center text-muted py-4">Belum ada admin cabang terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
