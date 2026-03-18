@extends('app')

@section('title', $course->title . ' - MoocsPangarti')

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
                    @if($canManageCourse)
                        <a href="{{ route('courses.participants', $course) }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-people-fill"></i> Lihat Peserta
                        </a>
                    @endif
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
                    @php
                        $existingCert = $user ? \App\Models\Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first() : null;
                    @endphp
                    @if($existingCert)
                        <div class="card border-0 mb-0" style="background: linear-gradient(135deg, rgba(164,53,240,0.08), rgba(86,36,208,0.05)); border: 1px solid rgba(164,53,240,0.2) !important; border-radius: 12px;">
                            <div class="card-body p-3 text-center">
                                <i class="bi bi-award-fill text-primary mb-2" style="font-size: 1.8rem;"></i>
                                <div class="fw-bold mb-1">🎉 Course Selesai!</div>
                                <small class="text-muted d-block mb-2">{{ $existingCert->certificate_number }}</small>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('certificates.show', $existingCert) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    <a href="{{ route('certificates.download', $existingCert) }}" class="btn btn-sm btn-primary flex-fill">
                                        <i class="bi bi-download"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border-0 mb-0" style="background: linear-gradient(135deg, rgba(30,155,90,0.08), rgba(30,155,90,0.04)); border: 1px solid rgba(30,155,90,0.25) !important; border-radius: 12px;">
                            <div class="card-body p-3 text-center">
                                <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 1.8rem;"></i>
                                <div class="fw-bold mb-1 text-success">Course Completed!</div>
                                <small class="text-muted d-block mb-2">Klaim sertifikat Anda sekarang.</small>
                                <form method="POST" action="{{ route('courses.claim-certificate', $course) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-award"></i> Dapatkan Sertifikat
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Modules List (grouped by section) --}}
    <div class="col-md-8 col-xl-9">
        @php
            $sections = $course->sections()->with(['modules' => fn($q) => $q->orderBy('order')])->orderBy('order')->get();
            $unsectionedModules = $course->modules->whereNull('section_id')->sortBy('order');
        @endphp

        {{-- Section list --}}

        @forelse($sections as $sectionIndex => $section)
            <div class="card mb-3">
                {{-- Section Header --}}
                <div class="card-header d-flex justify-content-between align-items-start" style="background: rgba(164,53,240,0.06); border-bottom: 2px solid rgba(164,53,240,0.15);">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: var(--primary); font-size: 0.75rem;">BAB {{ $sectionIndex + 1 }}</span>
                            <h5 class="mb-0 fw-bold">{{ $section->title }}</h5>
                        </div>
                        @if($section->description)
                            <p class="text-muted small mb-0 mt-1">{{ $section->description }}</p>
                        @endif
                    </div>
                    @if($canManageCourse)
                        <div class="d-flex gap-1 flex-shrink-0 ms-3">
                            <a href="{{ route('sections.edit', [$course, $section]) }}" class="btn btn-sm btn-outline-secondary" title="Edit Bab">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('sections.destroy', [$course, $section]) }}" onsubmit="return confirm('Hapus bab ini? Modul-modulnya akan menjadi tidak tergabung bab.')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Bab">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Modules in this section --}}
                <div class="list-group list-group-flush">
                    @forelse($section->modules as $module)
                        @include('courses._module_row', compact('module','course','user','canManageCourse'))
                    @empty
                        <div class="list-group-item text-center py-3 text-muted">
                            <i class="bi bi-inbox"></i> Belum ada modul dalam bab ini.
                        </div>
                    @endforelse
                </div>

                @if($canManageCourse)
                    <div class="card-footer bg-white py-2 d-flex justify-content-center">
                        @include('modules._type_dropup', [
                            'course'   => $course,
                            'section'  => $section,
                            'btnClass' => 'btn-primary',
                            'btnSize'  => 'btn-sm',
                        ])
                    </div>
                @endif
            </div>
        @empty
        @endforelse

        {{-- Tambah Bab button — below all sections --}}
        @if($canManageCourse)
            <div class="mb-3">
                <a href="{{ route('sections.create', $course) }}" class="btn btn-outline-primary">
                    <i class="bi bi-collection-play"></i> Tambah Bab
                </a>
            </div>
        @endif

        {{-- Unsectioned modules --}}
        @if($unsectionedModules->isNotEmpty() || $sections->isEmpty())
            <div class="card mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-collection-play text-primary"></i>
                            {{ $sections->isEmpty() ? 'Modul' : 'Modul Tanpa Bab' }}
                        </h5>
                        <small class="text-muted">Struktur materi course</small>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($unsectionedModules as $module)
                        @include('courses._module_row', compact('module','course','user','canManageCourse'))
                    @empty
                        <div class="p-4">
                            <p class="text-muted text-center mb-3">Belum ada modul di luar bab.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($course->modules->isEmpty() && $sections->isEmpty())
            <div class="card mb-3">
                <div class="p-4 text-center">
                    <p class="text-muted mb-3">No modules available in this course</p>
                    @if($canManageCourse)
                        <a href="{{ route('sections.create', $course) }}" class="btn btn-outline-primary me-2">
                            <i class="bi bi-collection"></i> Tambah Bab
                        </a>
                        @include('modules._type_dropup', ['course' => $course, 'btnClass' => 'btn-primary', 'btnSize' => ''])
                    @endif
                </div>
            </div>
        @endif

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
