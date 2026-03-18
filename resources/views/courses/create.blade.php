@extends('app')

@section('title', 'Tambah Course - MoocsPangarti')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">
            <i class="bi bi-plus-square"></i> Tambah Course Baru
        </h1>
        <p class="text-muted mb-0">Instructor dapat menyimpan course sebagai draft atau mengirimkannya ke admin untuk approval sebelum tayang ke publik.</p>
    </div>
    <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Kembali ke Courses
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
                    @php($submitLabel = 'Simpan Course')
                    @php($cancelRoute = route('courses.index'))
                    @include('courses.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
