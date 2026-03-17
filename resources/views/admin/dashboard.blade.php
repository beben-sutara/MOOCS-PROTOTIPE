@extends('app')

@section('title', 'Admin Dashboard - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div>
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-shield-check"></i> Platform control center
            </span>
            <h1 class="section-title mb-2">Admin dashboard untuk memantau kesehatan platform secara menyeluruh.</h1>
            <p class="section-subtitle mb-0">Sekarang admin punya workspace khusus untuk melihat user, course, enrollment, dan progres modul tanpa bercampur dengan dashboard learner biasa.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3">
                    <i class="bi bi-people"></i>
                </div>
                <p class="text-muted mb-2">Total Users</p>
                <h3 class="mb-1">{{ $totalUsers }}</h3>
                <small class="text-muted">{{ $totalStudents }} student • {{ $totalInstructors }} instructor • {{ $totalAdmins }} admin</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(164, 53, 240, 0.08); color: var(--primary);">
                    <i class="bi bi-journal-richtext"></i>
                </div>
                <p class="text-muted mb-2">Total Courses</p>
                <h3 class="mb-1">{{ $totalCourses }}</h3>
                <small class="text-muted">{{ $publishedCourses }} published • {{ $pendingApprovalCourses }} pending • {{ $draftCourses }} draft • {{ $archivedCourses }} archived</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(30, 155, 90, 0.12); color: var(--success);">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <p class="text-muted mb-2">Enrollments</p>
                <h3 class="mb-1">{{ $totalEnrollments }}</h3>
                <small class="text-muted">{{ $activeEnrollments }} active • {{ $completedEnrollments }} completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(180, 105, 14, 0.12); color: var(--warning);">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <p class="text-muted mb-2">Completion Rate</p>
                <h3 class="mb-1">{{ $completionRate }}%</h3>
                <small class="text-muted">{{ $completedModules }} module progress selesai dari {{ $totalModules }} modul</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart text-primary"></i> Distribusi Platform</h5>
            </div>
            <div class="card-body p-4">
                <div class="surface-muted p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Users vs Course</span>
                        <strong>{{ $totalUsers }} / {{ $totalCourses }}</strong>
                    </div>
                </div>
                <div class="surface-muted p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Enrollment aktif</span>
                        <strong>{{ $activeEnrollments }}</strong>
                    </div>
                </div>
                <div class="surface-muted p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Pengajuan instructor pending</span>
                        <strong>{{ $pendingInstructorApplications }}</strong>
                    </div>
                </div>
                <div class="surface-muted p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Progress modul selesai</span>
                        <strong>{{ $completedModules }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-fire text-primary"></i> Course Terpopuler</h5>
                    <small class="text-muted">Diurutkan berdasarkan jumlah enrollment</small>
                </div>
                <a href="{{ route('courses.manage') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($popularCourses as $course)
                    <div class="list-group-item py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="d-flex gap-3">
                                <div style="width: 110px; min-width: 110px;" class="overflow-hidden rounded border">
                                    @include('courses.thumbnail', ['course' => $course, 'height' => '74px'])
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $course->title }}</h6>
                                    <p class="text-muted mb-2">{{ \Illuminate\Support\Str::limit($course->description, 110) }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-info">{{ $course->instructor?->name ?? 'Tanpa instructor' }}</span>
                                        <span class="badge bg-secondary">{{ $course->modules_count }} modul</span>
                                        <span class="badge bg-success">{{ $course->enrollments_count }} enrollment</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            </div>
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
</div>

<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-plus text-primary"></i> User Terbaru</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Kelola User</a>
                </div>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentUsers as $recentUser)
                    <div class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold">{{ $recentUser->name }}</div>
                            <small class="text-muted">{{ $recentUser->email }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $recentUser->role === 'admin' ? 'bg-danger' : ($recentUser->role === 'instructor' ? 'bg-primary' : 'bg-secondary') }}">
                                {{ ucfirst($recentUser->role) }}
                            </span>
                            <div class="small text-muted mt-1">{{ $recentUser->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-4 text-muted">
                        Belum ada user baru.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-journal-text text-primary"></i> Course Terbaru</h5>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentCourses as $recentCourse)
                    <div class="list-group-item py-3 d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold">{{ $recentCourse->title }}</div>
                            <small class="text-muted">Instructor: {{ $recentCourse->instructor?->name ?? 'Tanpa instructor' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $recentCourse->status_badge_class }}">
                                {{ $recentCourse->status_label }}
                            </span>
                            <div class="small text-muted mt-1">{{ $recentCourse->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-4 text-muted">
                        Belum ada course baru.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
