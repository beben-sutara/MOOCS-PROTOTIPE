@extends('app')

@section('title', 'Home - MOOC Platform')

@section('content')
{{-- Hero Section --}}
<div class="hero">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <span class="market-badge mb-3">
                <i class="bi bi-stars"></i> Learn, build, and teach with confidence
            </span>
            <h1>Belajar online dengan tampilan modern ala course marketplace premium.</h1>
            <p>Temukan course, kelola modul, pantau progres, dan bangun pengalaman belajar yang terasa lebih profesional untuk student maupun instructor.</p>
            @if(Auth::check())
                <div class="d-flex flex-wrap gap-3">
                    <a href="/courses" class="btn btn-primary btn-lg">
                        <i class="bi bi-play-fill"></i> Mulai Belajar
                    </a>
                    @if(Auth::user()->role !== 'user')
                        <a href="{{ route('courses.manage') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-kanban"></i> Kelola Course
                        </a>
                    @endif
                </div>
            @else
                <div class="d-flex flex-wrap gap-3">
                    <a href="/register" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Daftar Sekarang
                    </a>
                    <a href="/login" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </div>
            @endif

            <div class="row mt-4 g-3">
                <div class="col-sm-4">
                    <div class="surface-muted p-3 h-100">
                        <div class="fw-bold">{{ $stats['total_courses'] ?? 0 }}+</div>
                        <small class="text-muted">Course aktif</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="surface-muted p-3 h-100">
                        <div class="fw-bold">{{ $stats['total_modules'] ?? 0 }}+</div>
                        <small class="text-muted">Modul interaktif</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="surface-muted p-3 h-100">
                        <div class="fw-bold">{{ $stats['total_users'] ?? 0 }}+</div>
                        <small class="text-muted">Learner terdaftar</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="soft-panel p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="text-muted small">Top learning experience</div>
                        <h4 class="mb-0">Marketplace-style learning hub</h4>
                    </div>
                    <div class="feature-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                </div>
                <div class="surface-muted p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Course management</span>
                        <span class="text-primary fw-bold">Instructor-ready</span>
                    </div>
                    <small class="text-muted">Tambah course, upload thumbnail, susun modul, dan kelola status publikasi.</small>
                </div>
                <div class="surface-muted p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Student progress</span>
                        <span class="text-primary fw-bold">Tracked</span>
                    </div>
                    <small class="text-muted">Progress, prerequisite, dan gamification tetap jadi bagian inti platform.</small>
                </div>
                <div class="surface-muted p-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Visual refresh</span>
                        <span class="text-primary fw-bold">Modern</span>
                    </div>
                    <small class="text-muted">Nuansa terang, rapi, dan fokus ke marketplace-course experience.</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistics --}}
<div class="row mb-5">
    <div class="col-md-4 mb-3">
        <div class="stat-card h-100">
            <h3>{{ $stats['total_users'] ?? 0 }}</h3>
            <p><i class="bi bi-people"></i> Active learners</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card h-100">
            <h3>{{ $stats['total_courses'] ?? 0 }}</h3>
            <p><i class="bi bi-book"></i> Published courses</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card h-100">
            <h3>{{ $stats['total_modules'] ?? 0 }}</h3>
            <p><i class="bi bi-play-btn"></i> Learning modules</p>
        </div>
    </div>
</div>

<div class="mb-4">
    <h2 class="section-title">Kenapa tampilannya sekarang terasa lebih premium?</h2>
    <p class="section-subtitle">Kami ubah pendekatan visual platform agar lebih dekat ke experience course marketplace modern: fokus ke konten, CTA jelas, dan navigasi instructor lebih rapi.</p>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="feature-icon mb-3">
                    <i class="bi bi-controller"></i>
                </div>
                <h5 class="card-title">Gamifikasi tetap kuat</h5>
                <p class="card-text text-muted">Dapatkan XP, naik level, dan nikmati dashboard progres yang lebih clean.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="feature-icon mb-3">
                    <i class="bi bi-lock"></i>
                </div>
                <h5 class="card-title">Structured learning path</h5>
                <p class="card-text text-muted">Prerequisite module dan akses bertahap membuat journey belajar tetap terarah.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="feature-icon mb-3">
                    <i class="bi bi-kanban"></i>
                </div>
                <h5 class="card-title">Instructor workflow</h5>
                <p class="card-text text-muted">Instructor bisa bikin course, upload thumbnail, dan mengelola modul dari satu alur yang rapi.</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-5 mb-4">
    <h2 class="section-title">Course yang siap Anda ambil hari ini</h2>
    <p class="section-subtitle">Pengunjung sekarang bisa langsung melihat course yang tersedia dari halaman depan, memilih yang diminati, lalu lanjut login atau signup untuk otomatis enroll ke course tersebut.</p>
</div>

<div class="row">
    @forelse($featuredCourses as $course)
        @php
            $isEnrolled = Auth::check() ? Auth::user()->enrollments()->where('course_id', $course->id)->exists() : false;
        @endphp

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card h-100 course-card">
                <div class="overflow-hidden rounded-top">
                    @include('courses.thumbnail', ['course' => $course, 'height' => '200px'])
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <h5 class="card-title mb-0">{{ $course->title }}</h5>
                        <span class="badge {{ $course->status_badge_class }}">{{ $course->status_label }}</span>
                    </div>
                    <p class="card-text text-muted mb-3">{{ \Illuminate\Support\Str::limit($course->description, 120) }}</p>

                    <div class="mb-4">
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-person"></i> Instructor: {{ $course->instructor?->name ?? 'Admin' }}
                        </small>
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-collection-play"></i> {{ $course->modules->count() }} modules
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-people"></i> {{ $course->enrollments_count }} peserta terdaftar
                        </small>
                    </div>

                    <div class="mt-auto d-grid gap-2">
                        @if(Auth::check())
                            @if($isEnrolled)
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-primary">
                                    <i class="bi bi-play-fill"></i> Lanjutkan Course
                                </a>
                            @else
                                <form method="POST" action="{{ route('courses.enroll', $course) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Enroll Sekarang
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('register', ['course' => $course->id]) }}" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Pilih & Signup Course
                            </a>
                            <a href="{{ route('login', ['course' => $course->id]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-in-right"></i> Sudah punya akun? Login
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada course published yang bisa ditampilkan di halaman depan.
            </div>
        </div>
    @endforelse
</div>

@if(Auth::check())
    <div class="mt-5">
        <div class="soft-panel p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="market-badge mb-3">
                        <i class="bi bi-person-circle"></i> Personal learning hub
                    </span>
                    <h2 class="section-title mb-2">Profil dan progres Anda tetap jadi pusat pengalaman.</h2>
                    <p class="section-subtitle mb-4">Akses cepat ke dashboard, course yang sedang dipelajari, dan progres XP Anda dalam tampilan yang lebih ringan.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="/dashboard" class="btn btn-primary">
                            <i class="bi bi-speedometer2"></i> Lihat Dashboard
                        </a>
                        <a href="/courses" class="btn btn-outline-secondary">
                            <i class="bi bi-book"></i> Jelajahi Course
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="surface-muted p-4">
                        <div class="level-badge mb-3" style="width: auto; font-size: 1.5rem;">
                            Level {{ Auth::user()->level }}
                        </div>
                        <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                        <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                        <div class="mb-2 fw-semibold">Total XP: {{ Auth::user()->xp }}</div>
                        <div class="xp-bar mb-2">
                            <div class="xp-bar-fill" data-progress-width="{{ Auth::user()->getXpProgress() }}"></div>
                        </div>
                        <small class="text-muted">{{ Auth::user()->getXpInCurrentLevel() }} / {{ Auth::user()->next_level_xp - Auth::user()->getTotalXpForCurrentLevel() }} XP</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
