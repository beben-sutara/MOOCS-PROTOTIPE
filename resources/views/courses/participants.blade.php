@extends('app')

@section('title', 'Peserta: ' . $course->title . ' - MoocsPangarti')

@section('content')

{{-- Breadcrumb & Header --}}
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none text-muted">Courses</a></li>
        <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-muted">{{ Illuminate\Support\Str::limit($course->title, 40) }}</a></li>
        <li class="breadcrumb-item active">Peserta</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold">Daftar Peserta</h1>
        <p class="text-muted mb-0 small">
            <i class="bi bi-journal-richtext me-1"></i>
            {{ $course->title }}
            @if($course->instructor)
                &nbsp;·&nbsp;<i class="bi bi-person me-1"></i>{{ $course->instructor->name }}
            @endif
        </p>
    </div>
    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Course
    </a>
</div>

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body py-3 px-2">
                <div class="h4 mb-1 fw-bold">{{ $stats['total'] }}</div>
                <div class="small text-muted">Total Peserta</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center" style="border-top: 3px solid var(--primary);">
            <div class="card-body py-3 px-2">
                <div class="h4 mb-1 fw-bold text-primary">{{ $stats['active'] }}</div>
                <div class="small text-muted">Sedang Belajar</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center" style="border-top: 3px solid var(--success, #1e9b5a);">
            <div class="card-body py-3 px-2">
                <div class="h4 mb-1 fw-bold text-success">{{ $stats['completed'] }}</div>
                <div class="small text-muted">Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center" style="border-top: 3px solid #6c757d;">
            <div class="card-body py-3 px-2">
                <div class="h4 mb-1 fw-bold text-secondary">{{ $stats['dropped'] }}</div>
                <div class="small text-muted">Dropped</div>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filter --}}
<div class="card mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('courses.participants', $course) }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari peserta</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                       class="form-control" placeholder="Nama atau email peserta">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected($statusFilter === 'active')>Sedang Belajar</option>
                    <option value="completed" @selected($statusFilter === 'completed')>Selesai</option>
                    <option value="dropped" @selected($statusFilter === 'dropped')>Dropped</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Terapkan
                </button>
                <a href="{{ route('courses.participants', $course) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Participants Table --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-people-fill text-primary"></i> Daftar Peserta</h5>
            <small class="text-muted">
                @if($search !== '' || $statusFilter !== '')
                    Menampilkan {{ $enrollments->count() }} dari {{ $stats['total'] }} peserta sesuai filter
                @else
                    {{ $stats['total'] }} peserta terdaftar
                @endif
            </small>
        </div>
        <span class="badge bg-dark">{{ $enrollments->count() }} hasil</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Peserta</th>
                    <th>Status</th>
                    <th style="min-width: 160px;">Progress Modul</th>
                    <th>Tanggal Daftar</th>
                    @if($totalModules > 0)
                        <th>Selesai</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                    @php
                        $completed = $completedMap[$enrollment->user_id] ?? 0;
                        $pct = $totalModules > 0 ? round(($completed / $totalModules) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $enrollment->user?->name ?? '(akun dihapus)' }}</div>
                            <div class="small text-muted">{{ $enrollment->user?->email ?? '-' }}</div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($enrollment->status) {
                                    'completed' => 'bg-success',
                                    'dropped'   => 'bg-secondary',
                                    default     => 'bg-primary',
                                };
                                $statusLabel = match($enrollment->status) {
                                    'completed' => 'Selesai',
                                    'dropped'   => 'Dropped',
                                    default     => 'Aktif',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            @if($enrollment->completed_at)
                                <div class="small text-muted mt-1">{{ $enrollment->completed_at->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td>
                            @if($totalModules > 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar"
                                             style="width: {{ $pct }}%"
                                             aria-valuenow="{{ $pct }}"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small text-muted" style="min-width: 38px;">{{ $pct }}%</span>
                                </div>
                            @else
                                <span class="text-muted small">Belum ada modul</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $enrollment->enrolled_at?->format('d M Y') ?? '-' }}</div>
                            <div class="small text-muted">{{ $enrollment->enrolled_at?->diffForHumans() ?? '' }}</div>
                        </td>
                        @if($totalModules > 0)
                            <td>
                                <span class="{{ $completed === $totalModules && $totalModules > 0 ? 'text-success fw-semibold' : '' }}">
                                    {{ $completed }} / {{ $totalModules }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="feature-icon mx-auto mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            @if($search !== '' || $statusFilter !== '')
                                <p class="text-muted mb-2">Tidak ada peserta yang cocok dengan filter.</p>
                                <a href="{{ route('courses.participants', $course) }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                            @else
                                <p class="text-muted mb-0">Belum ada peserta yang mendaftar ke course ini.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
