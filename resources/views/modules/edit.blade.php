@extends('app')

@section('title', 'Edit Modul - MoocsPangarti')

@section('content')
@php
    use App\Models\Module;
    $icons = Module::typeIcons();
    $labels = Module::typeLabels();
    $currentType    = $module->type ?? Module::TYPE_TEXT;
    $needsMultipart = in_array($currentType, [Module::TYPE_FILE, Module::TYPE_AUDIO]);
    $quickAdd        = false;
    $presetSectionId = null;
@endphp
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none text-muted">Courses</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-muted">{{ Illuminate\Support\Str::limit($course->title, 40) }}</a></li>
        <li class="breadcrumb-item active">Edit Modul</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">
            <i class="bi {{ $icons[$currentType] ?? 'bi-pencil-square' }} text-primary"></i>
            Edit Modul
            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.7rem; vertical-align: middle;">
                {{ $labels[$currentType] ?? $currentType }}
            </span>
        </h1>
        <p class="text-muted mb-0 small">Perbarui isi dan aturan akses modul pada course <strong>{{ $course->title }}</strong>.</p>
    </div>
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Course
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('modules.update', ['course' => $course, 'module' => $module]) }}"
                      @if($needsMultipart) enctype="multipart/form-data" @endif>
                    @method('PUT')
                    @php($submitLabel = 'Perbarui Modul')
                    @include('modules.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
