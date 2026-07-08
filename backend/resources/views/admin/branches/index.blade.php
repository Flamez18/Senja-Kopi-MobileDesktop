@extends('admin.layouts.app')

@section('title', 'Kelola Cabang')

@section('header_title', 'Kelola Cabang')

@section('content')
<div class="card card-premium p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="font-weight-700 text-dark m-0"><i class="bi bi-shop me-2 text-warning"></i> Daftar Cabang Kopi Senja</h5>
            <p class="text-muted small m-0">Kelola operasional, jam buka-tutup, dan status aktif cabang kafe Anda.</p>
        </div>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-coffee">
            <i class="bi bi-plus-lg me-1"></i> Tambah Cabang
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-premium align-middle table-hover m-0">
            <thead>
                <tr>
                    <th>Nama Cabang</th>
                    <th>Alamat</th>
                    <th>Kota</th>
                    <th>Telepon</th>
                    <th>Jam Operasional</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td class="font-weight-700 text-dark">{{ $branch->name }}</td>
                    <td><small class="text-muted">{{ $branch->address }}</small></td>
                    <td>{{ $branch->city }}</td>
                    <td>{{ $branch->phone ?? '-' }}</td>
                    <td class="font-weight-600">
                        {{ substr($branch->open_time, 0, 5) }} - {{ substr($branch->close_time, 0, 5) }}
                    </td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-coffee">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini? Semua data admin cabang dan stok terkait juga akan terhapus.')">
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
                    <td colspan="7" class="text-center text-muted py-4">Belum ada cabang terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
