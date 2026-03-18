@extends('app')

@section('title', 'Tambah Modul - MoocsPangarti')

@section('content')
@php
    use App\Models\Module;
    $labels = Module::typeLabels();
    $icons  = Module::typeIcons();
    $currentType    = $module->type ?? Module::TYPE_TEXT;
    $needsMultipart = in_array($currentType, [Module::TYPE_FILE, Module::TYPE_AUDIO]);
    $quickAdd       = $quickAdd ?? false;
    $presetSectionId = $presetSectionId ?? null;
    // Section name for breadcrumb/subtitle hint
    $presetSection = $presetSectionId
        ? $course->sections()->find($presetSectionId)
        : null;
@endphp
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none text-muted">Courses</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-muted">{{ Illuminate\Support\Str::limit($course->title, 40) }}</a></li>
        <li class="breadcrumb-item active">Tambah Modul</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">
            <i class="bi {{ $icons[$currentType] ?? 'bi-journal-plus' }} text-primary"></i>
            Tambah Modul — {{ $labels[$currentType] ?? 'Baru' }}
        </h1>
        <p class="text-muted mb-0 small">
            Tambahkan materi baru ke course <strong>{{ $course->title }}</strong>
            @if($presetSection)
                &rsaquo; Bab <strong>{{ $presetSection->title }}</strong>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if(!$quickAdd)
            @include('modules._type_dropup', ['course' => $course, 'btnClass' => 'btn-outline-primary', 'btnSize' => 'btn-sm'])
        @endif
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Course
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('modules.store', $course) }}"
                      @if($needsMultipart) enctype="multipart/form-data" @endif>
                    @php($submitLabel = 'Simpan Modul')
                    @include('modules.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

