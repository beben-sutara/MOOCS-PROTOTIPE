@extends('app')

@section('title', 'Kelola Kursus - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-kanban-fill"></i> Ruang kerja instruktur
            </span>
            <h1 class="section-title mb-2">Kelola seluruh kursus Anda dari satu dashboard khusus.</h1>
            <p class="section-subtitle mb-0">Halaman ini sekarang diselaraskan dengan tema marketplace-course baru supaya alur kerja instruktur terasa lebih premium dan mudah dipindai.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-book"></i> Lihat katalog
            </a>
            <a href="{{ route('courses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Kursus baru
            </a>
        </div>
    </div>
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
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>
                                <a href="{{ route('modules.create', $course) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-journal-plus"></i> Modul
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
@endsection
