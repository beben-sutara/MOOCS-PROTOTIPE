@extends('app')

@section('title', 'Ajukan Menjadi Instructor - MOOC Platform')

@section('content')
<div class="soft-panel p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="market-badge mb-3">
                <i class="bi bi-person-workspace"></i> Instructor application
            </span>
            <h1 class="section-title mb-2">Ajukan diri Anda sebagai instructor.</h1>
            <p class="section-subtitle mb-0">Ceritakan keahlian dan motivasi Anda. Admin akan mereview pengajuan sebelum akses instructor diaktifkan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke dashboard
            </a>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person-circle"></i> Lihat profil
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Ringkasan status</h5>

                @if($latestApplication)
                    <div class="surface-muted rounded-4 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Status terakhir</span>
                            <span class="badge {{ $latestApplication->status_badge_class }}">{{ $latestApplication->status_label }}</span>
                        </div>
                        <div class="small text-muted">Dikirim {{ $latestApplication->created_at->diffForHumans() }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Bidang keahlian</div>
                        <div class="fw-semibold">{{ $latestApplication->expertise }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Motivasi</div>
                        <div>{{ $latestApplication->motivation }}</div>
                    </div>

                    @if($latestApplication->experience)
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Pengalaman</div>
                            <div>{{ $latestApplication->experience }}</div>
                        </div>
                    @endif

                    @if($latestApplication->admin_notes)
                        <div class="alert {{ $latestApplication->status === 'rejected' ? 'alert-danger' : 'alert-info' }} mb-0">
                            <strong>Catatan admin:</strong>
                            <div class="mt-1">{{ $latestApplication->admin_notes }}</div>
                        </div>
                    @endif
                @else
                    <div class="surface-muted rounded-4 p-3 mb-3">
                        <div class="fw-semibold mb-2">Belum ada pengajuan</div>
                        <div class="text-muted small">Lengkapi form di samping untuk memulai proses menjadi instructor.</div>
                    </div>
                @endif

                <div class="surface-muted rounded-4 p-3">
                    <div class="fw-semibold mb-2">Apa yang terjadi setelah apply?</div>
                    <small class="d-block mb-2 text-muted">1. Admin mereview profil, keahlian, dan motivasi Anda.</small>
                    <small class="d-block mb-2 text-muted">2. Jika disetujui, role akun Anda berubah menjadi instructor.</small>
                    <small class="d-block text-muted">3. Setelah itu Anda langsung bisa membuat dan mengelola course.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-send-check text-primary"></i> Form pengajuan instructor</h5>
            </div>
            <div class="card-body p-4">
                @if($latestApplication && $latestApplication->status === 'pending')
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-hourglass-split"></i> Pengajuan Anda masih diproses admin. Anda bisa mengirim pengajuan baru setelah pengajuan ini selesai direview.
                    </div>
                @else
                    <form method="POST" action="{{ route('instructor.apply.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="expertise" class="form-label">Bidang keahlian</label>
                            <input
                                type="text"
                                id="expertise"
                                name="expertise"
                                class="form-control @error('expertise') is-invalid @enderror"
                                value="{{ old('expertise', $latestApplication?->status === 'rejected' ? $latestApplication->expertise : '') }}"
                                placeholder="Contoh: Web Development, UI/UX, Data Science"
                                required
                            >
                            @error('expertise')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="motivation" class="form-label">Mengapa Anda ingin menjadi instructor?</label>
                            <textarea
                                id="motivation"
                                name="motivation"
                                rows="5"
                                class="form-control @error('motivation') is-invalid @enderror"
                                required
                            >{{ old('motivation', $latestApplication?->status === 'rejected' ? $latestApplication->motivation : '') }}</textarea>
                            @error('motivation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 20 karakter.</small>
                        </div>

                        <div class="mb-4">
                            <label for="experience" class="form-label">Pengalaman relevan (opsional)</label>
                            <textarea
                                id="experience"
                                name="experience"
                                rows="4"
                                class="form-control @error('experience') is-invalid @enderror"
                                placeholder="Ceritakan pengalaman mengajar, membangun produk, sertifikasi, atau portofolio singkat."
                            >{{ old('experience', $latestApplication?->status === 'rejected' ? $latestApplication->experience : '') }}</textarea>
                            @error('experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim pengajuan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
