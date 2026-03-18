@extends('app')

@section('title', 'Sertifikat Saya - MoocsPangarti')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-award"></i> Sertifikat Saya
            </span>
            <h1 class="section-title mb-2">Sertifikat Penyelesaian Course</h1>
            <p class="section-subtitle mb-0">Semua sertifikat yang Anda peroleh dari course yang telah diselesaikan.</p>
        </div>
        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-book"></i> Explore Courses
        </a>
    </div>
</div>

@if($certificates->isEmpty())
    <div class="card text-center py-5">
        <div class="card-body">
            <div class="feature-icon mx-auto mb-3" style="background: rgba(164, 53, 240, 0.08); color: var(--primary);">
                <i class="bi bi-award" style="font-size: 2rem;"></i>
            </div>
            <h4 class="mb-2">Belum Ada Sertifikat</h4>
            <p class="text-muted mb-4">Selesaikan semua modul dalam sebuah course untuk mendapatkan sertifikat.</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-right"></i> Mulai Belajar
            </a>
        </div>
    </div>
@else
    <div class="row">
        @foreach($certificates as $certificate)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feature-icon" style="background: rgba(164, 53, 240, 0.1); color: var(--primary); width: 48px; height: 48px; min-width: 48px; font-size: 1.4rem;">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $certificate->course->title }}</h6>
                                <small class="text-muted">{{ $certificate->course->instructor->name }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nomor Sertifikat</small>
                            <code class="text-primary fw-bold">{{ $certificate->certificate_number }}</code>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Diterbitkan</small>
                            <span>{{ $certificate->issued_at->format('d F Y') }}</span>
                        </div>

                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-download"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
