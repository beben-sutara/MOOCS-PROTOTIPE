@extends('app')

@section('title', 'Kelola Course - MoocsPangarti')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb mb-0" style="font-size: 0.82rem;">
                <li class="breadcrumb-item">
                    <a href="/" class="text-decoration-none" style="color: var(--muted);">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('courses.index') }}" class="text-decoration-none" style="color: var(--muted);">Courses</a>
                </li>
                <li class="breadcrumb-item active" style="color: var(--muted);">Kelola Course</li>
            </ol>
        </nav>
        <h1 class="section-title mb-1">Kelola Course</h1>
        <p class="section-subtitle mb-0">Pantau dan kelola seluruh course Anda dari satu tempat.</p>
    </div>
    <a href="{{ route('courses.create') }}" class="btn btn-primary align-self-start mt-1">
        <i class="bi bi-plus-circle"></i> Tambah Course
    </a>
</div>

{{-- Sub-Navigation Tabs --}}
<div class="courses-subnav mb-4">
    <ul class="courses-nav-tabs">
        <li>
            <a href="{{ route('courses.index') }}" class="courses-nav-link">
                <i class="bi bi-collection"></i> Semua Course
            </a>
        </li>
        <li>
            <a href="{{ route('courses.index') }}" class="courses-nav-link">
                <i class="bi bi-bookmark-check"></i> Course Saya
            </a>
        </li>
        <li class="ms-auto">
            <span class="courses-nav-link active" aria-current="page">
                <i class="bi bi-kanban"></i> Kelola Course
                <span class="courses-nav-count">{{ $manageableCourses->count() }}</span>
            </span>
        </li>
    </ul>
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
                <p class="text-muted mb-0">Menunggu persetujuan</p>
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
                <p class="text-muted mb-0">Dipublikasikan</p>
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
                <p class="text-muted mb-0">Diarsipkan</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-journal-richtext text-primary"></i> Daftar kursus</h5>
            <small class="text-muted">Semua kursus yang bisa Anda kelola</small>
        </div>
        <span class="badge bg-dark">{{ $manageableCourses->count() }} kursus</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kursus</th>
                    <th>Status</th>
                    <th>Instruktur</th>
                    <th>Modul</th>
                    <th>Enroll</th>
                    <th>Dibuat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manageableCourses as $course)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 90px; min-width: 90px;" class="overflow-hidden rounded border">
                                    @include('courses.thumbnail', ['course' => $course, 'height' => '64px'])
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $course->title }}</div>
                                    <div class="small text-muted">{{ Str::limit($course->description, 90) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $course->status_badge_class }}">
                                {{ $course->status_label }}
                            </span>
                        </td>
                        <td>{{ $course->instructor->name ?? '-' }}</td>
                        <td>{{ $course->modules->count() }}</td>
                        <td>{{ $course->enrollments_count }}</td>
                        <td>{{ $course->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <a href="{{ route('courses.participants', $course) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-people"></i> Peserta
                                </a>
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <p class="text-muted mb-3">Belum ada kursus yang bisa Anda kelola.</p>
                            <a href="{{ route('courses.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Buat kursus pertama
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.courses-subnav {
    border-bottom: 2px solid var(--border);
}

.courses-nav-tabs {
    list-style: none;
    padding: 0;
    margin: 0 0 -2px;
    display: flex;
    align-items: center;
    gap: 0;
}

.courses-nav-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.75rem 1.15rem;
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--muted);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    transition: color 0.15s ease, border-color 0.15s ease;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.courses-nav-link:hover {
    color: var(--primary);
    background: rgba(164, 53, 240, 0.04);
}

.courses-nav-link.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.courses-nav-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.35rem;
    height: 1.35rem;
    padding: 0 0.35rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    background: var(--primary);
    color: #fff;
}
</style>
@endsection
