@extends('app')

@section('title', 'Pengajuan Instructor - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-person-workspace"></i> Review instructor
            </span>
            <h1 class="section-title mb-2">Kelola pengajuan instructor dari pengguna.</h1>
            <p class="section-subtitle mb-0">Admin bisa mereview motivasi, pengalaman, lalu menyetujui atau menolak permintaan menjadi instructor.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Dashboard admin
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(180, 105, 14, 0.12); color: var(--warning);">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="display-6 mb-2">{{ $pendingCount }}</div>
                <p class="text-muted mb-0">Menunggu review</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(30, 155, 90, 0.12); color: var(--success);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="display-6 mb-2">{{ $approvedCount }}</div>
                <p class="text-muted mb-0">Disetujui</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="feature-icon mx-auto mb-3" style="background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="display-6 mb-2">{{ $rejectedCount }}</div>
                <p class="text-muted mb-0">Ditolak</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.instructor-applications.index') }}" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label">Cari pengajuan</label>
                <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama, email, keahlian, atau isi motivasi">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.instructor-applications.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @forelse($applications as $application)
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <h5 class="mb-0">{{ $application->user?->name ?? 'User tidak ditemukan' }}</h5>
                                <span class="badge {{ $application->status_badge_class }}">{{ $application->status_label }}</span>
                                <span class="badge text-bg-light border">Role saat ini: {{ $application->user?->role ?? '-' }}</span>
                            </div>

                            <div class="text-muted small mb-3">
                                <div>{{ $application->user?->email }}</div>
                                <div>Dikirim {{ $application->created_at->format('d M Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-muted mb-1">Bidang keahlian</div>
                                <div class="fw-semibold">{{ $application->expertise }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-muted mb-1">Motivasi</div>
                                <div>{{ $application->motivation }}</div>
                            </div>

                            @if($application->experience)
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Pengalaman</div>
                                    <div>{{ $application->experience }}</div>
                                </div>
                            @endif

                            @if($application->reviewer)
                                <div class="small text-muted">
                                    Direview oleh {{ $application->reviewer->name }}{{ $application->reviewed_at ? ' pada ' . $application->reviewed_at->format('d M Y H:i') : '' }}
                                </div>
                            @endif
                        </div>

                        <div style="max-width: 360px; width: 100%;">
                            @if($application->status === 'pending')
                                <form method="POST" action="{{ route('admin.instructor-applications.update', $application) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="status-{{ $application->id }}" class="form-label">Keputusan</label>
                                        <select id="status-{{ $application->id }}" name="status" class="form-select">
                                            <option value="approved">Setujui</option>
                                            <option value="rejected">Tolak</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="admin-notes-{{ $application->id }}" class="form-label">Catatan admin</label>
                                        <textarea id="admin-notes-{{ $application->id }}" name="admin_notes" rows="5" class="form-control @error('admin_notes') is-invalid @enderror" placeholder="Opsional saat approve, wajib saat reject.">{{ old('admin_notes') }}</textarea>
                                        @error('admin_notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check2-square"></i> Simpan keputusan
                                    </button>
                                </form>
                            @else
                                <div class="surface-muted rounded-4 p-3 h-100">
                                    <div class="fw-semibold mb-2">Hasil review</div>
                                    <div class="mb-2">
                                        <span class="badge {{ $application->status_badge_class }}">{{ $application->status_label }}</span>
                                    </div>
                                    @if($application->admin_notes)
                                        <div class="small text-muted mb-2">Catatan admin</div>
                                        <div>{{ $application->admin_notes }}</div>
                                    @else
                                        <div class="text-muted small">Tidak ada catatan tambahan.</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    Belum ada pengajuan instructor yang cocok dengan filter saat ini.
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
