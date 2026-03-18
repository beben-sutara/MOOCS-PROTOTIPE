<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * Issue a certificate for a user who completed a course.
     * Returns existing certificate if already issued.
     */
    public function issueCertificate(User $user, Course $course, Enrollment $enrollment): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Certificate::create([
            'user_id'            => $user->id,
            'course_id'          => $course->id,
            'enrollment_id'      => $enrollment->id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'issued_at'          => $enrollment->completed_at ?? now(),
        ]);
    }

    /**
     * Generate a PDF for the given certificate and return the DomPDF instance.
     */
    public function generatePdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $certificate->load(['user', 'course.instructor']);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
            'user'        => $certificate->user,
            'course'      => $certificate->course,
            'instructor'  => $certificate->course->instructor,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf;
    }
}
