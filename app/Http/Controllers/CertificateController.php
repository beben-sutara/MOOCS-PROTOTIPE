<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function __construct(protected CertificateService $certificateService)
    {
        $this->middleware('auth')->except('verify');
    }

    /**
     * List all certificates for the authenticated user.
     */
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())
            ->with(['course.instructor'])
            ->orderByDesc('issued_at')
            ->get();

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Show a single certificate detail page.
     */
    public function show(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403);
        }

        $certificate->load(['user', 'course.instructor']);

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Download certificate as PDF.
     */
    public function download(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403);
        }

        $pdf = $this->certificateService->generatePdf($certificate);

        $filename = 'Sertifikat-' . str_replace(' ', '-', $certificate->course->title) . '-' . $certificate->certificate_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Claim a certificate for an already-completed enrollment.
     * Idempotent — safe to call even if certificate already exists.
     */
    public function claim(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->first();

        if (! $enrollment) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Anda belum menyelesaikan course ini.');
        }

        $certificate = $this->certificateService->issueCertificate($user, $course, $enrollment);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Sertifikat berhasil diterbitkan!');
    }

    /**
     * Public verification page — no auth required.
     */
    public function verify(string $number)
    {
        $certificate = Certificate::where('certificate_number', $number)
            ->with(['user', 'course.instructor'])
            ->first();

        return view('certificates.verify', compact('certificate', 'number'));
    }
}
