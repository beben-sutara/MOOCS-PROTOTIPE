@extends('app')

@section('title', 'Admin Dashboard - MoocsPangarti')

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
.admin-stat-link { text-decoration: none; color: inherit; display: block; height: 100%; }
.admin-stat-link .card { border-top: 3px solid transparent; height: 100%; transition: border-color .15s; }
.admin-stat-link:hover .card { border-top-color: var(--primary); }
.admin-stat-link.stat-users:hover .card { border-top-color: #0ea5e9; }
.admin-stat-link.stat-courses:hover .card { border-top-color: var(--primary); }
.admin-stat-link.stat-apps:hover .card { border-top-color: #f59e0b; }
</style>

{{-- Page Header --}}
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item active">Admin Dashboard</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">Admin Dashboard</h1>
        <p class="text-muted mb-0 small">Pantau kesehatan platform dan lakukan aksi cepat dari sini.</p>
    </div>
    <span class="badge text-bg-secondary py-2 px-3">
        <i class="bi bi-shield-check me-1"></i> Admin
    </span>
</div>

{{-- Admin Sub-Nav --}}
<div class="admin-subnav">
    <nav class="admin-nav-tabs">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link active">
            <i class="bi bi-grid-1x2"></i> Overview
        </a>
        <a href="{{ route('admin.users.index') }}" class="admin-nav-link">
            <i class="bi bi-people"></i> Users
        </a>
        <a href="{{ route('admin.instructor-applications.index') }}" class="admin-nav-link">
            <i class="bi bi-person-workspace"></i> Instructor Applications
            @if($pendingInstructorApplications > 0)
                <span class="admin-nav-badge">{{ $pendingInstructorApplications }}</span>
            @endif
        </a>
        <a href="{{ route('admin.courses.index') }}" class="admin-nav-link">
            <i class="bi bi-shield-check"></i> Moderate Courses
        </a>
        <a href="{{ route('courses.manage') }}" class="admin-nav-link">
            <i class="bi bi-kanban"></i> Manage Courses
        </a>
    </nav>
</div>

{{-- Pending Alert --}}
@if($pendingInstructorApplications > 0)
<div class="alert d-flex align-items-center gap-3 mb-4" role="alert"
     style="background:#fffbeb; border:1px solid #f59e0b; border-left:4px solid #f59e0b;">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>{{ $pendingInstructorApplications }} pengajuan instructor</strong> menunggu review Anda.
    </div>
    <a href="{{ route('admin.instructor-applications.index') }}?status=pending"
       class="btn btn-warning btn-sm flex-shrink-0">
        Review Sekarang <i class="bi bi-arrow-right"></i>
    </a>
</div>
@endif

{{-- Stat Cards --}}
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3 mb-4">
        <a href="{{ route('admin.users.index') }}" class="admin-stat-link stat-users">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon mx-auto mb-3" style="background:rgba(14,165,233,.1);color:#0ea5e9;">
                        <i class="bi bi-people"></i>
                    </div>
                    <p class="text-muted mb-1 small">Total Users</p>
                    <h3 class="mb-1 fw-bold">{{ $totalUsers }}</h3>
                    <p class="text-muted mb-2" style="font-size:.78rem;">
                        {{ $totalStudents }} student · {{ $totalInstructors }} instructor · {{ $totalAdmins }} admin
                    </p>
                    <span class="small text-primary fw-semibold">Kelola Users →</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
        <a href="{{ route('admin.courses.index') }}" class="admin-stat-link stat-courses">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon mx-auto mb-3" style="background:rgba(164,53,240,.08);color:var(--primary);">
                        <i class="bi bi-journal-richtext"></i>
                    </div>
                    <p class="text-muted mb-1 small">Total Courses</p>
                    <h3 class="mb-1 fw-bold">{{ $totalCourses }}</h3>
                    <p class="text-muted mb-2" style="font-size:.78rem;">
                        {{ $publishedCourses }} published · {{ $pendingApprovalCourses }} pending · {{ $draftCourses }} draft
                    </p>
                    <span class="small text-primary fw-semibold">Moderate Courses →</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background:rgba(30,155,90,.12);color:var(--success);">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <p class="text-muted mb-1 small">Enrollments</p>
                <h3 class="mb-1 fw-bold">{{ $totalEnrollments }}</h3>
                <p class="text-muted mb-2" style="font-size:.78rem;">
                    {{ $activeEnrollments }} aktif · {{ $completedEnrollments }} selesai
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
        <a href="{{ route('admin.instructor-applications.index') }}" class="admin-stat-link stat-apps">
            <div class="card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon mx-auto mb-3" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <p class="text-muted mb-1 small">Completion Rate</p>
                    <h3 class="mb-1 fw-bold">{{ $completionRate }}%</h3>
                    <p class="text-muted mb-2" style="font-size:.78rem;">
                        {{ $completedModules }} / {{ $totalModules }} modul selesai
                    </p>
                    <span class="small text-primary fw-semibold">Lihat Pengajuan →</span>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Popular Courses & Platform Summary --}}
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-fire text-primary"></i> Course Terpopuler</h5>
                    <small class="text-muted">Diurutkan berdasarkan jumlah enrollment</small>
                </div>
                <a href="{{ route('courses.manage') }}" class="btn btn-sm btn-outline-secondary">Kelola Course</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($popularCourses as $course)
                    <div class="list-group-item py-3">
                        <div class="d-flex gap-3 align-items-center">
                            <div style="width:90px;min-width:90px;" class="overflow-hidden rounded border">
                                @include('courses.thumbnail', ['course' => $course, 'height' => '60px'])
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="mb-1 text-truncate">{{ $course->title }}</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-info bg-opacity-25 text-info-emphasis border" style="font-size:.7rem;">
                                        {{ $course->instructor?->name ?? 'Tanpa instructor' }}
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:.7rem;">
                                        {{ $course->modules_count }} modul
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success border" style="font-size:.7rem;">
                                        {{ $course->enrollments_count }} enrollment
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary btn-sm flex-shrink-0">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-4 text-muted">
                        Belum ada course untuk dianalisis.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line text-primary"></i> Ringkasan Platform</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Completion Rate</span>
                        <span class="fw-bold text-success">{{ $completionRate }}%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Modul selesai</span>
                        <span class="fw-bold">{{ $completedModules }} / {{ $totalModules }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Enrollment aktif</span>
                        <span class="fw-bold">{{ $activeEnrollments }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Course published</span>
                        <span class="fw-bold text-success">{{ $publishedCourses }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Pending review</span>
                        <span class="fw-bold {{ $pendingApprovalCourses > 0 ? 'text-warning' : '' }}">{{ $pendingApprovalCourses }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="text-muted small">Instructor pending</span>
                        <span class="fw-bold {{ $pendingInstructorApplications > 0 ? 'text-danger' : '' }}">{{ $pendingInstructorApplications }}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-shield-check me-1"></i> Moderate Courses
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Recent Users & Recent Courses --}}
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-plus text-primary"></i> User Terbaru</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Kelola User</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentUsers as $recentUser)
                    <div class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-truncate">{{ $recentUser->name }}</div>
                            <small class="text-muted">{{ $recentUser->email }}</small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge {{ $recentUser->role === 'admin' ? 'bg-danger' : ($recentUser->role === 'instructor' ? 'bg-primary' : 'bg-secondary') }}">
                                {{ ucfirst($recentUser->role) }}
                            </span>
                            <div class="small text-muted mt-1">{{ $recentUser->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-4 text-muted">Belum ada user baru.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-journal-text text-primary"></i> Course Terbaru</h5>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary">Moderate</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentCourses as $recentCourse)
                    <div class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-truncate">{{ $recentCourse->title }}</div>
                            <small class="text-muted">{{ $recentCourse->instructor?->name ?? 'Tanpa instructor' }}</small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge {{ $recentCourse->status_badge_class }}">
                                {{ $recentCourse->status_label }}
                            </span>
                            <div class="small text-muted mt-1">{{ $recentCourse->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-4 text-muted">Belum ada course baru.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
