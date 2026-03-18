@extends('app')

@section('title', 'Moderate Courses - MoocsPangarti')

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
        <li class="breadcrumb-item active">Moderate Courses</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">Moderate Courses</h1>
        <p class="text-muted mb-0 small">Ubah status course ke draft, published, atau archived tanpa membuka form edit penuh.</p>
    </div>
</div>

{{-- Admin Sub-Nav --}}
<div class="admin-subnav">
    <nav class="admin-nav-tabs">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">
            <i class="bi bi-grid-1x2"></i> Overview
        </a>
        <a href="{{ route('admin.users.index') }}" class="admin-nav-link">
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
        <a href="{{ route('admin.courses.index') }}" class="admin-nav-link active">
            <i class="bi bi-shield-check"></i> Moderate Courses
        </a>
        <a href="{{ route('courses.manage') }}" class="admin-nav-link">
            <i class="bi bi-kanban"></i> Manage Courses
        </a>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(180, 105, 14, 0.12); color: var(--warning);">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="display-6 text-warning mb-2">{{ $draftCount }}</div>
                <p class="text-muted mb-0">Draft</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(13, 202, 240, 0.12); color: #0dcaf0;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="display-6 text-info mb-2">{{ $pendingApprovalCount }}</div>
                <p class="text-muted mb-0">Pending Approval</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(30, 155, 90, 0.12); color: var(--success);">
                    <i class="bi bi-broadcast"></i>
                </div>
                <div class="display-6 text-success mb-2">{{ $publishedCount }}</div>
                <p class="text-muted mb-0">Published</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(108, 117, 125, 0.12); color: #6c757d;">
                    <i class="bi bi-archive"></i>
                </div>
                <div class="display-6 text-secondary mb-2">{{ $archivedCount }}</div>
                <p class="text-muted mb-0">Archived</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="search" class="form-label">Cari course</label>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Judul course, deskripsi, nama instructor, atau email">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Filter status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="pending_approval" @selected($status === 'pending_approval')>Pending Approval</option>
                    <option value="published" @selected($status === 'published')>Published</option>
                    <option value="archived" @selected($status === 'archived')>Archived</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-journal-richtext text-primary"></i> Daftar Course</h5>
            <small class="text-muted">Menampilkan hingga 50 course terbaru sesuai filter</small>
        </div>
        <span class="badge bg-dark">{{ $courses->count() }} / {{ $totalCourses }} course</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Stats</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th class="text-end">Moderasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 100px; min-width: 100px;" class="overflow-hidden rounded border">
                                    @include('courses.thumbnail', ['course' => $course, 'height' => '68px'])
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $course->title }}</div>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($course->description, 90) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $course->instructor?->name ?? 'Tanpa instructor' }}</div>
                            <div class="small text-muted">{{ $course->instructor?->email ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="small text-muted">{{ $course->modules_count }} modul</div>
                            <div class="small text-muted">{{ $course->enrollments_count }} enrollment</div>
                        </td>
                        <td>
                            <span class="badge {{ $course->status_badge_class }}">
                                {{ $course->status_label }}
                            </span>
                        </td>
                        <td>{{ $course->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <div class="d-flex flex-column align-items-end gap-2">
                                <div class="btn-group" role="group" aria-label="Course moderation">
                                    <form method="POST" action="{{ route('admin.courses.status.update', $course) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="draft">
                                        <button type="submit" class="btn btn-sm {{ $course->status === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                            Draft
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.courses.status.update', $course) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="pending_approval">
                                        <button type="submit" class="btn btn-sm {{ $course->status === 'pending_approval' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                            Review
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.courses.status.update', $course) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="published">
                                        <button type="submit" class="btn btn-sm {{ $course->status === 'published' ? 'btn-primary' : 'btn-outline-primary' }}">
                                            Publish
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.courses.status.update', $course) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="archived">
                                        <button type="submit" class="btn btn-sm {{ $course->status === 'archived' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                            Archive
                                        </button>
                                    </form>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    <a href="{{ route('courses.participants', $course) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-people"></i> Peserta
                                    </a>
                                    <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada course yang cocok dengan filter moderasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
