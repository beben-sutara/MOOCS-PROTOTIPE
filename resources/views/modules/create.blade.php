@extends('app')

@section('title', 'Tambah Modul - MOOC Platform')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">
            <i class="bi bi-journal-plus"></i> Tambah Modul
        </h1>
        <p class="text-muted mb-0">Tambahkan materi baru ke course `{{ $course->title }}`.</p>
    </div>
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Kembali ke Course
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('modules.store', $course) }}">
                    @php($submitLabel = 'Simpan Modul')
                    @include('modules.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
