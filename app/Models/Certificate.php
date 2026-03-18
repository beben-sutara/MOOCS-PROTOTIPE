<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'certificate_number',
        'issued_at',
        'file_path',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public static function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . now()->year . '-' . strtoupper(Str::random(6));
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }

    public function getVerifyUrl(): string
    {
        return route('certificates.verify', $this->certificate_number);
    }
}
