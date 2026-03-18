@extends('app')

@section('title', 'Dashboard - MoocsPangarti')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-speedometer2"></i> Learning dashboard
            </span>
            <h1 class="section-title mb-2">Dashboard pembelajaran Anda kini terasa lebih rapi dan premium.</h1>
            <p class="section-subtitle mb-0">Pantau course aktif, progres belajar, dan workflow instructor dari satu halaman yang lebih mirip platform course marketplace modern.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-book"></i> Explore Courses
            </a>
            @if(Auth::user()->role === 'user')
                <a href="{{ route('instructor.apply') }}" class="btn btn-outline-primary">
                    <i class="bi bi-person-workspace"></i> Apply as Instructor
                </a>
            @endif
            @if(Auth::user()->role !== 'user')
                <a href="{{ route('courses.manage') }}" class="btn btn-primary">
                    <i class="bi bi-kanban"></i> Manage Courses
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    @if(Auth::user()->role !== 'user')
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="mb-1">
                            <i class="bi bi-journal-plus"></i> Instructor Panel
                        </h4>
                        <p class="text-muted mb-0">
                            Kelola course Anda dan tambahkan course baru untuk peserta.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center">
                            <small class="text-muted d-block">Course dibuat</small>
                            <strong>{{ Auth::user()->instructedCourses()->count() }}</strong>
                        </div>
                        <a href="{{ route('courses.manage') }}" class="btn btn-outline-primary">
                            <i class="bi bi-kanban"></i> Kelola Course
                        </a>
                        <a href="{{ route('courses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Course
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="mb-1">
                            <i class="bi bi-person-workspace"></i> Jalur Menjadi Instructor
                        </h4>
                        @if($latestInstructorApplication?->status === 'pending')
                            <p class="text-muted mb-0">Pengajuan Anda sedang direview admin. Kami akan memberi akses instructor setelah disetujui.</p>
                        @elseif($latestInstructorApplication?->status === 'rejected')
                            <p class="text-muted mb-0">Pengajuan terakhir ditolak. Anda bisa memperbarui data dan mengajukan ulang.</p>
                        @else
                            <p class="text-muted mb-0">Punya keahlian yang ingin dibagikan? Ajukan diri sebagai instructor dan mulai membuat course.</p>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @if($latestInstructorApplication)
                            <div class="text-center">
                                <small class="text-muted d-block">Status terakhir</small>
                                <strong>{{ $latestInstructorApplication->status_label }}</strong>
                            </div>
                        @endif
                        <a href="{{ route('instructor.apply') }}" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                            {{ $latestInstructorApplication?->status === 'pending' ? 'Lihat Status' : 'Ajukan Sekarang' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Rank Card --}}
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(180, 105, 14, 0.12); color: var(--warning);">
                    <i class="bi bi-trophy"></i>
                </div>
                <p class="text-muted mb-2">Your Rank</p>
                <h3
                    class="text-warning metric-pop"
                    data-count-up="{{ $userRank['rank'] ?? 0 }}"
                    data-count-up-prefix="#"
                >#{{ $userRank['rank'] ?? 'N/A' }}</h3>
            </div>
        </div>
    </div>

    {{-- Courses Enrolled --}}
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3">
                    <i class="bi bi-book"></i>
                </div>
                <p class="text-muted mb-2">Courses</p>
                <h3 class="metric-pop" data-count-up="{{ Auth::user()->enrollments->count() }}">{{ Auth::user()->enrollments->count() }}</h3>
            </div>
        </div>
    </div>

    {{-- Completed Modules --}}
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(30, 155, 90, 0.12); color: var(--success);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <p class="text-muted mb-2">Modules Done</p>
                <h3 class="metric-pop" data-count-up="{{ Auth::user()->moduleProgress->where('is_completed', true)->count() }}">{{ Auth::user()->moduleProgress->where('is_completed', true)->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

@if(Auth::user()->role !== 'user')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-journal-richtext"></i> Course yang Anda Buat
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('courses.manage') }}" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-kanban"></i> Kelola
                        </a>
                        <a href="{{ route('courses.create') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-plus-circle"></i> Course Baru
                        </a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse(Auth::user()->instructedCourses()->latest()->get() as $course)
                        <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div style="width: 120px; min-width: 120px;" class="overflow-hidden rounded border">
                                @include('courses.thumbnail', ['course' => $course, 'height' => '80px'])
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $course->title }}</h6>
                                <p class="text-muted mb-1">{{ Str::limit($course->description, 120) }}</p>
                                <span class="badge {{ $course->status_badge_class }}">
                                    {{ $course->status_label }}
                                </span>
                            </div>
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Lihat Course
                            </a>
                        </div>
                    @empty
                        <div class="list-group-item text-center py-4 text-muted">
                            Anda belum membuat course apa pun.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Active Courses --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-book-half"></i> Active Courses
                </h5>
            </div>
            <div class="row p-3">
                @forelse(Auth::user()->enrollments()->with('course')->where('status', 'active')->get() as $enrollment)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    {{ $enrollment->course->title }}
                                </h5>
                                <p class="card-text text-muted small">
                                    {{ Str::limit($enrollment->course->description, 100) }}
                                </p>
                                
                                @php
                                    $totalModules = $enrollment->course->modules()->count();
                                    $completedModules = Auth::user()->moduleProgress()
                                        ->whereHas('module', function($q) use($enrollment) {
                                            $q->where('course_id', $enrollment->course_id);
                                        })
                                        ->where('is_completed', true)
                                        ->count();
                                    $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                                @endphp

                                <p class="small text-muted mb-2">
                                    {{ $completedModules }} / {{ $totalModules }} modules completed
                                </p>

                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                        data-progress-width="{{ $progress }}"
                                        aria-valuenow="{{ $progress }}" 
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <a href="/courses/{{ $enrollment->course->id }}" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-arrow-right"></i> Go to Course
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-md-12">
                        <p class="text-muted text-center py-4">
                            Belum ada course yang diikuti
                        </p>
                        <a href="/courses" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Browse Courses
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Completed Courses & Certificates (role: user only) --}}
@if(Auth::user()->role === 'user')
    @php
        $completedEnrollments = Auth::user()->enrollments()
            ->with('course')
            ->where('status', 'completed')
            ->latest('completed_at')
            ->get();
    @endphp
    @if($completedEnrollments->isNotEmpty())
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #a435f0, #5624d0); color: white;">
                        <h5 class="mb-0">
                            <i class="bi bi-award-fill"></i> Sertifikat Saya
                        </h5>
                        <a href="{{ route('certificates.index') }}" class="btn btn-sm btn-light">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="row p-3">
                        @foreach($completedEnrollments as $enrollment)
                            @php
                                $cert = \App\Models\Certificate::where('user_id', Auth::id())
                                    ->where('course_id', $enrollment->course_id)
                                    ->first();
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-award-fill text-primary" style="font-size: 1.4rem;"></i>
                                            <h6 class="mb-0">{{ $enrollment->course->title }}</h6>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            Selesai: {{ $enrollment->completed_at?->format('d M Y') ?? '-' }}
                                        </p>
                                        @if($cert)
                                            <code class="text-primary small mb-3">{{ $cert->certificate_number }}</code>
                                            <div class="d-flex gap-2 mt-auto">
                                                <a href="{{ route('certificates.show', $cert) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </a>
                                                <a href="{{ route('certificates.download', $cert) }}" class="btn btn-sm btn-primary flex-fill">
                                                    <i class="bi bi-download"></i> PDF
                                                </a>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Sertifikat tidak tersedia</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@endsection
