@extends('app')

@section('title', 'Sertifikat - ' . $certificate->course->title)

@section('content')
<div class="mb-4">
    <a href="{{ route('certificates.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Sertifikat Saya
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        {{-- Certificate Preview Card --}}
        <div class="card mb-4">
            <div class="card-body p-0 overflow-hidden" style="border-radius: 18px;">
                <div style="background: linear-gradient(135deg, #a435f0 0%, #5624d0 100%); padding: 3rem 2rem; text-align: center; color: white;">
                    <div class="mb-3">
                        <i class="bi bi-award-fill" style="font-size: 4rem; opacity: 0.9;"></i>
                    </div>
                    <p class="mb-1" style="font-size: 0.9rem; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.85;">MoocsPangarti — Certificate of Completion</p>
                    <h1 style="font-size: 2.2rem; font-weight: 700; margin: 1rem 0;">{{ $certificate->user->name }}</h1>
                    <p style="opacity: 0.9; font-size: 1.05rem;">telah berhasil menyelesaikan course</p>
                    <h2 style="font-size: 1.6rem; font-weight: 700; margin: 0.5rem 0 1.5rem;">{{ $certificate->course->title }}</h2>

                    <div style="display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 0.5rem 1.5rem;">
                        <small style="opacity: 0.8;">Diterbitkan pada</small><br>
                        <strong>{{ $certificate->issued_at->format('d F Y') }}</strong>
                    </div>
                </div>

                <div style="background: #f7f9fa; padding: 1.5rem 2rem; border-top: 1px solid #d1d7dc;">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Instructor</small>
                            <strong>{{ $certificate->course->instructor->name }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Nomor Sertifikat</small>
                            <code class="text-primary fw-bold">{{ $certificate->certificate_number }}</code>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Verifikasi</small>
                            <a href="{{ $certificate->getVerifyUrl() }}" target="_blank" class="text-primary small">
                                <i class="bi bi-shield-check"></i> Verifikasi Online
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary btn-lg">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <button class="btn btn-outline-secondary btn-lg" onclick="copyVerifyLink()">
                <i class="bi bi-link-45deg"></i> Salin Link Verifikasi
            </button>
            <a href="{{ $certificate->getVerifyUrl() }}" target="_blank" class="btn btn-outline-success btn-lg">
                <i class="bi bi-shield-check"></i> Verifikasi Publik
            </a>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
function copyVerifyLink() {
    const url = "{{ $certificate->getVerifyUrl() }}";
    navigator.clipboard.writeText(url).then(() => {
        alert('Link verifikasi berhasil disalin!');
    });
}
</script>
@endsection
