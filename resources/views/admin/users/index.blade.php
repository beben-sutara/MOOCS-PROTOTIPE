@extends('app')

@section('title', 'Kelola Pengguna - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-people-fill"></i> Manajemen pengguna
            </span>
            <h1 class="section-title mb-2">Kelola akun pengguna, instruktur, dan admin dari satu tempat.</h1>
            <p class="section-subtitle mb-0">Halaman ini memberi admin kontrol awal untuk mencari pengguna, melihat status akun, dan memperbarui identitas serta peran tanpa masuk ke database.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke dashboard admin
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="display-6 mb-2">{{ $totalUsers }}</div>
                <p class="text-muted mb-0">Total pengguna</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(164, 53, 240, 0.08); color: var(--primary);">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div class="display-6 mb-2">{{ $totalInstructors }}</div>
                <p class="text-muted mb-0">Instruktur</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(180, 105, 14, 0.12); color: var(--warning);">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="display-6 mb-2">{{ $totalAdmins }}</div>
                <p class="text-muted mb-0">Admin Aktif</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari pengguna</label>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama, email, atau nomor telepon">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Filter peran</label>
                <select id="role" name="role" class="form-select">
                    <option value="">Semua peran</option>
                    <option value="user" @selected($role === 'user')>Pengguna</option>
                    <option value="instructor" @selected($role === 'instructor')>Instruktur</option>
                    <option value="admin" @selected($role === 'admin')>Admin</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Terapkan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-person-lines-fill text-primary"></i> Daftar pengguna</h5>
            <small class="text-muted">Menampilkan hingga 50 akun terbaru sesuai filter</small>
        </div>
        <span class="badge bg-dark">{{ $users->count() }} hasil</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Pengguna</th>
                    <th>Peran</th>
                    <th>XP / Level</th>
                    <th>Aktivitas</th>
                    <th>Bergabung</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $managedUser)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $managedUser->name }}</div>
                            <div class="small text-muted">{{ $managedUser->email }}</div>
                            @if($managedUser->phone)
                                <div class="small text-muted">{{ $managedUser->phone }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $managedUser->role === 'admin' ? 'bg-danger' : ($managedUser->role === 'instructor' ? 'bg-primary' : 'bg-secondary') }}">
                                {{ $managedUser->role === 'admin' ? 'Admin' : ($managedUser->role === 'instructor' ? 'Instruktur' : 'Pengguna') }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $managedUser->xp }} XP</div>
                            <small class="text-muted">Level {{ $managedUser->level }}</small>
                        </td>
                        <td>
                            <div class="small text-muted">{{ $managedUser->instructed_courses_count }} kursus dibuat</div>
                            <div class="small text-muted">{{ $managedUser->enrollments_count }} pendaftaran</div>
                        </td>
                        <td>{{ $managedUser->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $managedUser) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square"></i> Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada pengguna yang cocok dengan filter saat ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
