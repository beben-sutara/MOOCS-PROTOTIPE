@extends('app')

@section('title', 'Verifikasi Sertifikat - MoocsPangarti')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center mb-4">
            <div class="feature-icon mx-auto mb-3" style="background: rgba(164, 53, 240, 0.08); color: var(--primary); width: 64px; height: 64px; font-size: 2rem;">
                <i class="bi bi-shield-check"></i>
            </div>
            <h2 class="section-title">Verifikasi Sertifikat</h2>
            <p class="text-muted">Nomor: <code class="text-primary">{{ $number }}</code></p>
        </div>

        @if($certificate)
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(30,155,90,0.12); color: #1e9b5a; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-success">Sertifikat Valid</h5>
                            <small class="text-muted">Sertifikat ini diterbitkan dan diverifikasi oleh MoocsPangarti</small>
                        </div>
                    </div>

                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0" style="width: 40%;">Pemegang</td>
                                <td class="fw-bold">{{ $certificate->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Course</td>
                                <td class="fw-bold">{{ $certificate->course->title }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Instructor</td>
                                <td>{{ $certificate->course->instructor->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Tanggal Diterbitkan</td>
                                <td>{{ $certificate->issued_at->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Nomor Sertifikat</td>
                                <td><code class="text-primary">{{ $certificate->certificate_number }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon mx-auto mb-3" style="background: rgba(179, 45, 15, 0.08); color: var(--danger); width: 56px; height: 56px; font-size: 1.6rem;">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <h5 class="text-danger mb-2">Sertifikat Tidak Ditemukan</h5>
                    <p class="text-muted mb-0">Nomor sertifikat <code>{{ $number }}</code> tidak terdaftar di sistem kami. Pastikan nomor yang dimasukkan sudah benar.</p>
                </div>
            </div>
        @endif

        <div class="text-center mt-3">
            <a href="/" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
