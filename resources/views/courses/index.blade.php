@extends('app')

@section('title', 'Courses - MoocsPangarti')

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
                <li class="breadcrumb-item active" style="color: var(--muted);">Courses</li>
            </ol>
        </nav>
        <h1 class="section-title mb-1">Course Marketplace</h1>
        <p class="section-subtitle mb-0">Temukan, daftar, dan lanjutkan belajar dari satu tempat.</p>
    </div>
    @if(Auth::user()->role !== 'user')
        <a href="{{ route('courses.create') }}" class="btn btn-primary align-self-start mt-1">
            <i class="bi bi-plus-circle"></i> Tambah Course
        </a>
    @endif
</div>

{{-- Sub-Navigation Tabs --}}
<div class="courses-subnav mb-4">
    <ul class="courses-nav-tabs" role="tablist">
        <li role="presentation">
            <button class="courses-nav-link active" id="all-tab"
                data-bs-toggle="tab" data-bs-target="#all"
                type="button" role="tab" aria-controls="all" aria-selected="true">
                <i class="bi bi-collection"></i> Semua Course
                <span class="courses-nav-count">{{ count($allCourses) }}</span>
            </button>
        </li>
        <li role="presentation">
            <button class="courses-nav-link" id="enrolled-tab"
                data-bs-toggle="tab" data-bs-target="#enrolled"
                type="button" role="tab" aria-controls="enrolled" aria-selected="false">
                <i class="bi bi-bookmark-check"></i> Course Saya
                @if(count($enrolledCourses) > 0)
                    <span class="courses-nav-count">{{ count($enrolledCourses) }}</span>
                @endif
            </button>
        </li>
        @if(Auth::user()->role !== 'user')
            <li role="presentation" class="ms-auto">
                <a href="{{ route('courses.manage') }}" class="courses-nav-link">
                    <i class="bi bi-kanban"></i> Kelola Course
                </a>
            </li>
        @endif
    </ul>
</div>

<div class="tab-content">
    {{-- Semua Course Tab --}}
    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
        <div class="row g-4">
            @forelse($allCourses as $course)
                @php
                    $isEnrolled = Auth::user()->enrollments()->where('course_id', $course['id'])->exists();
                    $totalModules = isset($course['modules']) && is_array($course['modules']) ? count($course['modules']) : 0;
                    $completedModules = Auth::user()->moduleProgress()
                        ->whereHas('module', function($q) use($course) {
                            $q->where('course_id', $course['id']);
                        })
                        ->where('is_completed', true)
                        ->count();
                    $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                @endphp

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-100 course-card">
                        <div class="overflow-hidden" style="border-radius: 18px 18px 0 0; position: relative;">
                            @include('courses.thumbnail', ['course' => (object) $course, 'height' => '175px'])
                            @if($isEnrolled)
                                <span class="badge bg-primary position-absolute top-0 end-0 m-2" style="z-index: 3;">
                                    <i class="bi bi-check2"></i> Enrolled
                                </span>
                            @endif
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1" style="font-size: 0.98rem; line-height: 1.4;">
                                {{ $course['title'] }}
                            </h5>

                            <p class="card-text text-muted mb-3" style="font-size: 0.84rem; flex-grow: 1;">
                                {{ Str::limit($course['description'], 95) }}
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-person text-primary"></i>
                                    {{ $course['instructor']['name'] ?? 'Admin' }}
                                </small>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-collection-play text-primary"></i>
                                    {{ $totalModules }} modul
                                </small>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-people text-primary"></i>
                                    {{ $course['enrollments_count'] ?? 0 }}
                                </small>
                            </div>

                            @if($isEnrolled && $totalModules > 0)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Progress belajar</small>
                                        <small class="fw-semibold" style="color: var(--primary);">{{ $progress }}%</small>
                                    </div>
                                    <div class="progress" style="height: 5px; border-radius: 999px; background: #e9ecef;">
                                        <div class="progress-bar" role="progressbar"
                                            data-progress-width="{{ $progress }}"
                                            aria-valuenow="{{ $progress }}"
                                            aria-valuemin="0" aria-valuemax="100"
                                            style="background: linear-gradient(90deg, var(--primary), var(--secondary));"></div>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $completedModules }}/{{ $totalModules }} selesai</small>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-white border-top p-3">
                            @if($isEnrolled)
                                <a href="/courses/{{ $course['id'] }}" class="btn btn-primary w-100">
                                    <i class="bi bi-play-fill"></i> Lanjutkan Belajar
                                </a>
                            @else
                                <button class="btn btn-outline-secondary w-100 enroll-course-trigger"
                                    data-course-id="{{ $course['id'] }}"
                                    data-course-name="{{ $course['title'] }}">
                                    <i class="bi bi-plus-circle"></i> Enroll Sekarang
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="soft-panel p-5 text-center">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="bi bi-collection"></i>
                        </div>
                        <h5 class="mb-2">Belum ada course tersedia</h5>
                        <p class="text-muted mb-0">Course yang sudah dipublikasikan akan muncul di sini.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Course Saya Tab --}}
    <div class="tab-pane fade" id="enrolled" role="tabpanel" aria-labelledby="enrolled-tab">
        <div class="row g-4">
            @forelse($enrolledCourses as $enrollment)
                @php
                    $course = $enrollment['course'];
                    $totalModules = isset($course['modules']) && is_array($course['modules']) ? count($course['modules']) : 0;
                    $completedModules = Auth::user()->moduleProgress()
                        ->whereHas('module', function($q) use($course) {
                            $q->where('course_id', $course['id']);
                        })
                        ->where('is_completed', true)
                        ->count();
                    $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                @endphp

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-100 course-card" style="border: 2px solid var(--primary);">
                        <div class="overflow-hidden" style="border-radius: 16px 16px 0 0; position: relative;">
                            @include('courses.thumbnail', ['course' => $course, 'height' => '175px'])
                            @if($progress === 100)
                                <span class="badge bg-success position-absolute top-0 end-0 m-2" style="z-index: 3;">
                                    <i class="bi bi-check-circle"></i> Selesai
                                </span>
                            @else
                                <span class="badge bg-primary position-absolute top-0 end-0 m-2" style="z-index: 3;">
                                    <i class="bi bi-play-circle"></i> Aktif
                                </span>
                            @endif
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1" style="font-size: 0.98rem; line-height: 1.4;">
                                {{ $course['title'] }}
                            </h5>

                            <p class="card-text text-muted mb-3" style="font-size: 0.84rem; flex-grow: 1;">
                                {{ Str::limit($course['description'], 95) }}
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-person text-primary"></i>
                                    {{ $course['instructor']['name'] ?? 'Admin' }}
                                </small>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-collection-play text-primary"></i>
                                    {{ $totalModules }} modul
                                </small>
                            </div>

                            <div class="mb-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">{{ $completedModules }}/{{ $totalModules }} modul selesai</small>
                                    <small class="fw-bold" style="color: {{ $progress === 100 ? 'var(--success)' : 'var(--primary)' }};">{{ $progress }}%</small>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 999px; background: #e9ecef;">
                                    <div class="progress-bar {{ $progress === 100 ? 'bg-success' : '' }}" role="progressbar"
                                        data-progress-width="{{ $progress }}"
                                        aria-valuenow="{{ $progress }}"
                                        aria-valuemin="0" aria-valuemax="100"
                                        @if($progress < 100) style="background: linear-gradient(90deg, var(--primary), var(--secondary));" @endif>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top p-3">
                            <a href="/courses/{{ $course['id'] }}" class="btn btn-primary w-100">
                                @if($progress === 100)
                                    <i class="bi bi-eye"></i> Lihat Course
                                @else
                                    <i class="bi bi-play-fill"></i> Lanjutkan Belajar
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="soft-panel p-5 text-center">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="bi bi-bookmark"></i>
                        </div>
                        <h5 class="mb-2">Belum ada course yang diikuti</h5>
                        <p class="text-muted mb-3">Mulai perjalanan belajar Anda dengan mendaftar ke course pertama.</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('all-tab').click()">
                            <i class="bi bi-collection"></i> Jelajahi Course
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Enroll Modal --}}
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title"><i class="bi bi-plus-circle text-primary"></i> Konfirmasi Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0">Apakah Anda ingin mendaftar ke course <strong id="courseName"></strong>?</p>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmEnrollBtn">
                    <i class="bi bi-check-circle"></i> Ya, Daftar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.course-card {
    overflow: hidden;
}

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
    background: rgba(164, 53, 240, 0.1);
    color: var(--primary);
    transition: background 0.15s ease, color 0.15s ease;
}

.courses-nav-link.active .courses-nav-count {
    background: var(--primary);
    color: #fff;
}

.card-footer.bg-white {
    background: #fff !important;
}
</style>

<script>
let enrollCourseId = null;

document.querySelectorAll('.enroll-course-trigger').forEach((button) => {
    button.addEventListener('click', function() {
        enrollCourseId = this.dataset.courseId;
        document.getElementById('courseName').textContent = this.dataset.courseName || 'course ini';

        const modal = new bootstrap.Modal(document.getElementById('enrollModal'));
        modal.show();
    });
});

document.getElementById('confirmEnrollBtn')?.addEventListener('click', async function() {
    if (!enrollCourseId) return;

    try {
        const response = await fetch(`/api/courses/${enrollCourseId}/enroll`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content || ''
            }
        });

        if (response.ok) {
            window.location.reload();
        } else {
            alert('Gagal mendaftar. Silakan coba lagi.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
    }
});
</script>
@endsection
