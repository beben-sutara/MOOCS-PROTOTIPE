@extends('app')

@section('title', 'Pertanyaan Kuis — ' . $module->title)

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li class="breadcrumb-item active">Kuis: {{ $module->title }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-patch-question-fill text-primary me-2"></i>{{ $module->title }}
            </h4>
            <div class="text-muted small mt-1">
                @if($module->quiz_duration)
                    <i class="bi bi-stopwatch me-1"></i>Timer: {{ $module->quiz_duration }} menit ·
                @endif
                {{ $questions->count() }} pertanyaan ·
                {{ $questions->sum('points') }} total poin
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('questions.import.form', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-upload me-1"></i>Import CSV
            </a>
            <a href="{{ route('questions.create', [$course, $module]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah Pertanyaan
            </a>
        </div>
    </div>

    {{-- Quiz settings summary --}}
    @if($module->quiz_one_attempt || $module->quiz_required_for_next)
        <div class="alert alert-secondary d-flex gap-3 align-items-start mb-4 py-2">
            <i class="bi bi-sliders mt-1"></i>
            <div class="small">
                @if($module->quiz_one_attempt)
                    <span class="badge bg-warning text-dark me-2">Sekali kerjakan</span>
                @endif
                @if($module->quiz_required_for_next)
                    <span class="badge bg-info text-dark me-2">Wajib selesai untuk lanjut</span>
                @endif
                <a href="{{ route('modules.edit', [$course, $module]) }}" class="text-muted ms-1">Edit pengaturan</a>
            </div>
        </div>
    @endif

    {{-- Empty state --}}
    @if($questions->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="text-muted fs-1 mb-3"><i class="bi bi-question-circle"></i></div>
            <h5 class="text-muted">Belum ada pertanyaan</h5>
            <p class="text-muted small mb-4">Tambah pertanyaan satu per satu atau import dari file CSV.</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('questions.import.form', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-upload me-1"></i>Import CSV
                </a>
                <a href="{{ route('questions.create', [$course, $module]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pertanyaan
                </a>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($questions as $i => $q)
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-light text-dark border fw-normal small">{{ $i + 1 }}</span>
                                    @php
                                        $typeBadge = [
                                            'multiple_choice' => ['bg-primary', 'Pilihan Ganda'],
                                            'true_false'      => ['bg-success', 'Benar/Salah'],
                                            'essay'           => ['bg-secondary', 'Esai'],
                                        ][$q->type] ?? ['bg-secondary', $q->type];
                                    @endphp
                                    <span class="badge {{ $typeBadge[0] }} bg-opacity-75 small">{{ $typeBadge[1] }}</span>
                                    <span class="text-muted small"><i class="bi bi-star me-1"></i>{{ $q->points }} poin</span>
                                </div>
                                <p class="mb-2 fw-semibold">{{ $q->question }}</p>

                                @if($q->type !== 'essay')
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($q->options as $opt)
                                            <span class="badge {{ $opt->is_correct ? 'bg-success' : 'bg-light text-dark border' }} small">
                                                @if($opt->is_correct)<i class="bi bi-check-circle-fill me-1"></i>@endif
                                                {{ $opt->option_text }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($q->explanation)
                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-lightbulb me-1"></i>{{ $q->explanation }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('questions.edit', [$course, $module, $q]) }}" class="btn btn-outline-secondary btn-sm py-1 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('questions.destroy', [$course, $module, $q]) }}" method="POST"
                                      onsubmit="return confirm('Hapus pertanyaan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Course
        </a>
    </div>
</div>
@endsection
