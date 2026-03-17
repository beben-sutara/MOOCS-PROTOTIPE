@extends('app')

@section('title', 'Courses - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-collection-play-fill"></i> Course marketplace
            </span>
            <h1 class="section-title mb-2">Temukan course terbaik untuk dilanjutkan hari ini.</h1>
            <p class="section-subtitle mb-0">Nuansa baru ini dibuat supaya daftar course terasa lebih dekat ke pengalaman marketplace pembelajaran modern.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(Auth::user()->role !== 'user')
                <a href="{{ route('courses.manage') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-kanban"></i> Manage
                </a>
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Course
                </a>
            @endif

            <a href="/" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

{{-- Course Filter Tabs --}}
<ul class="nav nav-pills mb-4 gap-2" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
            <i class="bi bi-collection"></i> All Courses
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="enrolled-tab" data-bs-toggle="tab" data-bs-target="#enrolled" type="button" role="tab" aria-controls="enrolled" aria-selected="false">
            <i class="bi bi-bookmark-check"></i> My Courses
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- All Courses Tab --}}
    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
        <div class="row">
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

                <div class="col-md-6 mb-4">
                    <div class="card h-100 course-card">
                        <div class="overflow-hidden rounded-top">
                            @include('courses.thumbnail', ['course' => (object) $course, 'height' => '190px'])
                        </div>
                        <div class="card-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-0">{{ $course['title'] }}</h5>
                                @if($course['status'] === 'published')
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($course['status']) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            <p class="card-text text-muted">
                                {{ Str::limit($course['description'], 150) }}
                            </p>

                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-person"></i> Instructor: 
                                    {{ $course['instructor']['name'] ?? 'Admin' }}
                                </small>
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-collection-play"></i> 
                                    {{ $totalModules }} modules
                                </small>
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-people"></i>
                                    {{ $course['enrollments_count'] ?? 0 }} peserta terdaftar
                                </small>

                                @if($isEnrolled)
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" 
                                            data-progress-width="{{ $progress }}"
                                            aria-valuenow="{{ $progress }}" 
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">
                                        Progress: {{ $completedModules }}/{{ $totalModules }} modules ({{ $progress }}%)
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            @if($isEnrolled)
                                <a href="/courses/{{ $course['id'] }}" class="btn btn-primary w-100">
                                    <i class="bi bi-play-fill"></i> Continue Learning
                                </a>
                            @else
                                <button class="btn btn-outline-primary w-100 enroll-course-trigger" data-course-id="{{ $course['id'] }}" data-course-name="{{ $course['title'] }}">
                                    <i class="bi bi-plus-circle"></i> Enroll Now
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> Belum ada course tersedia
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- My Courses Tab --}}
    <div class="tab-pane fade" id="enrolled" role="tabpanel" aria-labelledby="enrolled-tab">
        <div class="row">
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

                <div class="col-md-6 mb-4">
                    <div class="card h-100 course-card border-primary" style="border-width: 2px;">
                        <div class="overflow-hidden rounded-top">
                            @include('courses.thumbnail', ['course' => $course, 'height' => '190px'])
                        </div>
                        <div class="card-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-0">{{ $course['title'] }}</h5>
                                <span class="badge bg-primary">Enrolled</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <p class="card-text text-muted">
                                {{ Str::limit($course['description'], 150) }}
                            </p>

                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-person"></i> Instructor: 
                                    {{ $course['instructor']['name'] ?? 'Admin' }}
                                </small>
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-collection-play"></i> 
                                    {{ $totalModules }} modules
                                </small>
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-people"></i>
                                    {{ $course['enrollments_count'] ?? 0 }} peserta terdaftar
                                </small>

                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                        data-progress-width="{{ $progress }}"
                                        aria-valuenow="{{ $progress }}" 
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $completedModules }}/{{ $totalModules }} modules completed ({{ $progress }}%)
                                </small>
                            </div>

                            @if($progress === 100)
                                <div class="alert alert-success mb-3" role="alert">
                                    <i class="bi bi-check-circle"></i> Completed!
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-light">
                            <a href="/courses/{{ $course['id'] }}" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-right"></i> View Course
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Anda belum mendaftar di course manapun
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('all-tab').click()">
                        <i class="bi bi-arrow-left"></i> Cari Course
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Enroll Modal --}}
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to enroll in <strong id="courseName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmEnrollBtn">Enroll</button>
            </div>
        </div>
    </div>
</div>

<style>
.course-card {
    overflow: hidden;
}
</style>

<script>
let enrollCourseId = null;

document.querySelectorAll('.enroll-course-trigger').forEach((button) => {
    button.addEventListener('click', function() {
        enrollCourseId = this.dataset.courseId;
        document.getElementById('courseName').textContent = this.dataset.courseName || 'this course';

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
            // Reload page to show updated status
            window.location.reload();
        } else {
            alert('Failed to enroll. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
});
</script>
@endsection
