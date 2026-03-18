@extends('app')

@section('title', 'Tambah Pertanyaan — ' . $module->title)

@section('content')
<div class="container py-4" style="max-width:720px">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('questions.index', [$course, $module]) }}">{{ $module->title }}</a></li>
            <li class="breadcrumb-item active">Tambah Pertanyaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Pertanyaan
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('questions.store', [$course, $module]) }}" method="POST">
                @csrf
                @include('questions.form', ['question' => new \App\Models\Question()])
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('questions.index', [$course, $module]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan Pertanyaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
