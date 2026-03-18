@extends('app')

@section('title', 'Kelola Pengguna - MoocsPangarti')

@section('content')
<style>
.admin-subnav { border-bottom: 2px solid var(--border); margin-bottom: 1.75rem; }
.admin-nav-tabs { display: flex; flex-wrap: wrap; margin: 0; padding: 0; }
.admin-nav-link {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.65rem 1.1rem; color: var(--muted); font-weight: 500;
    font-size: 0.9rem; text-decoration: none;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    transition: color .15s, border-color .15s; white-space: nowrap;
}
.admin-nav-link:hover { color: var(--primary); }
.admin-nav-link.active { color: var(--primary); border-bottom-color: var(--primary); }
.admin-nav-badge {
    display: inline-flex; align-items: center; justify-content: center;
    background: #dc3545; color: #fff; font-size: 0.68rem; font-weight: 700;
    border-radius: 10px; min-width: 18px; height: 18px; padding: 0 5px; line-height: 1;
}
</style>

{{-- Page Header --}}
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Admin Dashboard</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">Kelola Pengguna</h1>
        <p class="text-muted mb-0 small">Cari, lihat, dan perbarui akun pengguna, instruktur, dan admin.</p>
    </div>
</div>

{{-- Admin Sub-Nav --}}
<div class="admin-subnav">
    <nav class="admin-nav-tabs">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">
            <i class="bi bi-grid-1x2"></i> Overview
        </a>
        <a href="{{ route('admin.users.index') }}" class="admin-nav-link active">
            <i class="bi bi-people"></i> Users
        </a>
        <a href="{{ route('admin.instructor-applications.index') }}" class="admin-nav-link">
            <i class="bi bi-person-workspace"></i> Instructor Applications
            @isset($pendingInstructorApplications)
                @if($pendingInstructorApplications > 0)
                    <span class="admin-nav-badge">{{ $pendingInstructorApplications }}</span>
                @endif
            @endisset
        </a>
        <a href="{{ route('admin.courses.index') }}" class="admin-nav-link">
            <i class="bi bi-shield-check"></i> Moderate Courses
        </a>
        <a href="{{ route('courses.manage') }}" class="admin-nav-link">
            <i class="bi bi-kanban"></i> Manage Courses
        </a>
    </nav>
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
