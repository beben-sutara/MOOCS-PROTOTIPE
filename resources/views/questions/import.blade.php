@extends('app')

@section('title', 'Import Pertanyaan — ' . $module->title)

@section('content')
<div class="container py-4" style="max-width:640px">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('questions.index', [$course, $module]) }}">{{ $module->title }}</a></li>
            <li class="breadcrumb-item active">Import Pertanyaan</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-upload me-2 text-secondary"></i>Import Pertanyaan dari CSV
            </h5>
        </div>
        <div class="card-body">

            {{-- Format CSV --}}
            <div class="alert alert-info mb-4">
                <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle me-1"></i>Format CSV</h6>
                <div class="font-monospace small bg-white border rounded p-2 mb-2" style="overflow-x:auto;white-space:nowrap">
                    question,type,option_a,option_b,option_c,option_d,correct_answer,explanation,points
                </div>
                <ul class="small mb-2">
                    <li><strong>type</strong>: <code>multiple_choice</code>, <code>true_false</code>, atau <code>essay</code></li>
                    <li><strong>option_a–d</strong>: diisi untuk <code>multiple_choice</code>, kosong untuk lainnya</li>
                    <li><strong>correct_answer</strong>: <code>a</code>/<code>b</code>/<code>c</code>/<code>d</code> untuk pilihan ganda; <code>true</code>/<code>false</code> untuk benar/salah</li>
                    <li><strong>explanation</strong> dan <strong>points</strong>: opsional</li>
                </ul>
                <p class="small mb-0 fw-semibold">Contoh baris:</p>
                <div class="font-monospace small bg-white border rounded p-2" style="overflow-x:auto;white-space:nowrap">
                    "Ibukota Indonesia?",multiple_choice,Jakarta,Bandung,Surabaya,Medan,a,"Jakarta adalah ibukota RI",1<br>
                    "Bumi berbentuk bulat?",true_false,,,,,true,"",1<br>
                    "Jelaskan fotosintesis",essay,,,,,,"",2
                </div>
            </div>

            {{-- Download template --}}
            <a href="{{ route('questions.import.template', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm mb-4">
                <i class="bi bi-download me-1"></i>Download Template CSV
            </a>

            <form action="{{ route('questions.import.store', [$course, $module]) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="csv_file" class="form-label fw-semibold">Pilih File CSV <span class="text-danger">*</span></label>
                    <input
                        type="file"
                        class="form-control @error('csv_file') is-invalid @enderror"
                        id="csv_file"
                        name="csv_file"
                        accept=".csv,.txt"
                        required
                    >
                    @error('csv_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Ukuran maksimum: 2 MB. Format: .csv atau .txt</div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('questions.index', [$course, $module]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
