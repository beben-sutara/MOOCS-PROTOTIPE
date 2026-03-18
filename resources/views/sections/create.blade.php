@extends('app')

@section('title', 'Buat Bab - ' . $course->title)

@section('content')

<div class="mb-4">
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Course
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Info Card --}}
        <div class="card mb-4" style="border-left: 4px solid var(--primary);">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="feature-icon flex-shrink-0" style="background: rgba(164,53,240,0.1); color: var(--primary); width: 48px; height: 48px; font-size: 1.3rem;">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Buat Bab (Section)</h4>
                        <p class="text-muted mb-0">
                            Bab (bagian) adalah salah satu pembagian utama yang memuat satu pokok permasalahan.
                            Buat Bab untuk memisahkan tiap bagian utama dari keseluruhan materi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="card">
            <div class="card-body p-4">
                @include('sections.form', [
                    'submitLabel' => 'Simpan Bab',
                    'action'      => route('sections.store', $course),
                    'method'      => 'POST',
                ])
            </div>
        </div>

    </div>
</div>

@endsection
