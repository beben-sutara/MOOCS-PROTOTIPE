<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'order',
        'is_locked',
        'prerequisite_module_id',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(Module::class, 'prerequisite_module_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }
}
