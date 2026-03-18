<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail_path',
        'instructor_id',
        'status',
    ];

    protected $appends = [
        'thumbnail_url',
        'status_label',
        'status_badge_class',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return asset('storage/' . $this->thumbnail_path);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_approval' => 'Pending Approval',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'published' => 'bg-success',
            'draft' => 'bg-warning text-dark',
            'pending_approval' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
