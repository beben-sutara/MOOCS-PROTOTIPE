@extends('app')

@section('title', $course->title . ' - MOOC Platform')

@section('content')
<div class="soft-panel overflow-hidden mb-4">
    @include('courses.thumbnail', ['course' => $course, 'height' => '320px'])

    <div class="p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
            <div>
                <span class="market-badge mb-3">
                    <i class="bi bi-collection-play-fill"></i> Course detail
                </span>
                <h1 class="section-title mb-2">{{ $course->title }}</h1>
                <p class="section-subtitle mb-0">{{ $course->description }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($canManageCourse)
                    <a href="{{ route('modules.create', $course) }}" class="btn btn-primary">
                        <i class="bi bi-journal-plus"></i> Tambah Modul
                    </a>
                    <a href="{{ route('courses.edit', $course) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil-square"></i> Edit Course
                    </a>
                @endif
                <a href="/courses" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="badge {{ $course->status_badge_class }}">
                {{ $course->status_label }}
            </span>
            @if($course->instructor)
                <span class="badge bg-info">
                    <i class="bi bi-person"></i> {{ $course->instructor->name }}
                </span>
            @endif
            <span class="badge bg-secondary">
                <i class="bi bi-people"></i> {{ $course->enrollments_count }} peserta
            </span>
            @if($canManageCourse)
                <span class="badge bg-dark">
                    <i class="bi bi-gear"></i> Manage Mode
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 col-xl-3 mb-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-body p-4">
                <h5 class="mb-3">Course snapshot</h5>
                @php
                    $totalModules = $course->modules->count();
                    $completedModules = $user?->moduleProgress()
                        ->whereHas('module', function($q) use($course) {
                            $q->where('course_id', $course->id);
                        })
                        ->where('is_completed', true)
                        ->count() ?? 0;
                    $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                @endphp

                <div class="surface-muted p-3 mb-3">
                    <div class="text-muted small mb-1">Total modules</div>
                    <div class="fw-bold fs-4">{{ $totalModules }}</div>
                </div>

                <div class="surface-muted p-3 mb-3">
                    <div class="text-muted small mb-1">Total peserta</div>
                    <div class="fw-bold fs-4">{{ $course->enrollments_count }}</div>
                </div>

                <div class="surface-muted p-3 mb-3">
                    <div class="text-muted small mb-2">Progress</div>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar"
                            data-progress-width="{{ $progress }}"
                            aria-valuenow="{{ $progress }}"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">{{ $completedModules }} / {{ $totalModules }} completed</small>
                </div>

                @if($progress === 100)
                    <div class="alert alert-success mb-0" role="alert">
                        <i class="bi bi-check-circle"></i> Course completed!
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modules List --}}
    <div class="col-md-8 col-xl-9">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-collection-play text-primary"></i> Modules</h5>
                    <small class="text-muted">Struktur materi course Anda</small>
                </div>
                @if($canManageCourse)
                    <a href="{{ route('modules.create', $course) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Modul Baru
                    </a>
                @endif
            </div>

            <div class="list-group list-group-flush">
                @forelse($course->modules as $module)
                    @php
                        $userProgress = $user?->moduleProgress()
                            ->where('module_id', $module->id)
                            ->first();
                        $isCompleted = $userProgress?->is_completed ?? false;
                        $isViewed = $userProgress?->is_viewed ?? false;

                        // Check if module is accessible (prerequisite completed)
                        $isAccessible = !$module->prerequisite_module_id;
                        if ($module->prerequisite_module_id) {
                            $prereqCompleted = $user?->moduleProgress()
                                ->where('module_id', $module->prerequisite_module_id)
                                ->where('is_completed', true)
                                ->exists() ?? false;
                            $isAccessible = $prereqCompleted;
                        }
                    @endphp

                    <div class="list-group-item py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div style="min-width: 40px; text-align: center;">
                                        @if($isCompleted)
                                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                                        @elseif($isViewed)
                                            <i class="bi bi-play-circle-fill text-info" style="font-size: 1.5rem;"></i>
                                        @elseif(!$isAccessible)
                                            <i class="bi bi-lock-fill text-danger" style="font-size: 1.5rem;"></i>
                                        @else
                                            <i class="bi bi-play-circle text-muted" style="font-size: 1.5rem;"></i>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ $module->order }}. {{ $module->title }}</h5>
                                        <p class="text-muted mb-0 small">
                                            {{ Str::limit($module->content, 100) }}
                                        </p>
                                        @if(!$isAccessible)
                                            <small class="text-danger">
                                                <i class="bi bi-lock"></i> Complete "{{ $module->prerequisite?->title }}" to unlock
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                @if($canManageCourse)
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('courses.modules.show', ['course' => $course, 'module' => $module]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Preview
                                        </a>
                                        <a href="{{ route('modules.edit', ['course' => $course, 'module' => $module]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('modules.destroy', ['course' => $course, 'module' => $module]) }}" onsubmit="return confirm('Hapus modul ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    @if($isAccessible)
                                        @if($isCompleted)
                                            <span class="badge bg-success mb-2">Completed</span><br>
                                            <a href="/courses/{{ $course->id }}/modules/{{ $module->id }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Review
                                            </a>
                                        @else
                                            <a href="/courses/{{ $course->id }}/modules/{{ $module->id }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-play-fill"></i> Start Module
                                            </a>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-lock"></i> Locked
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4">
                        <p class="text-muted text-center mb-3">No modules available in this course</p>
                        @if($canManageCourse)
                            <div class="text-center">
                                <a href="{{ route('modules.create', $course) }}" class="btn btn-primary">
                                    <i class="bi bi-journal-plus"></i> Tambah Modul Pertama
                                </a>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
        @if($canManageCourse)
            <div class="mt-3 text-end">
                <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Hapus course ini beserta modul-modulnya?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Hapus Course
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
