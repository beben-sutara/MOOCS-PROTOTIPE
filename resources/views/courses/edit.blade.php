@extends('app')

@section('title', 'Edit Course - MoocsPangarti')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">
            <i class="bi bi-pencil-square"></i> Edit Course
        </h1>
        <p class="text-muted mb-0">Perbarui detail course Anda. Instructor hanya bisa menyimpan draft atau mengirim course ke admin untuk approval.</p>
    </div>
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Kembali ke Course
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @php($submitLabel = 'Perbarui Course')
                    @php($cancelRoute = route('courses.show', $course))
                    @include('courses.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
