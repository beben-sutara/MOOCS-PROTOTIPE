@extends('app')

@section('title', 'Edit Pertanyaan — ' . $module->title)

@section('content')
<div class="container py-4" style="max-width:720px">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('questions.index', [$course, $module]) }}">{{ $module->title }}</a></li>
            <li class="breadcrumb-item active">Edit Pertanyaan</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Pertanyaan
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('questions.update', [$course, $module, $question]) }}" method="POST">
                @csrf
                @method('PUT')
                @include('questions.form')
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('questions.index', [$course, $module]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i>Perbarui Pertanyaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
