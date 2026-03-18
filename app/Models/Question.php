<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_TRUE_FALSE      = 'true_false';
    const TYPE_ESSAY           = 'essay';

    public static function allTypes(): array
    {
        return [self::TYPE_MULTIPLE_CHOICE, self::TYPE_TRUE_FALSE, self::TYPE_ESSAY];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_MULTIPLE_CHOICE => 'Pilihan Ganda',
            self::TYPE_TRUE_FALSE      => 'Benar / Salah',
            self::TYPE_ESSAY           => 'Esai',
        ];
    }

    protected $fillable = [
        'module_id',
        'type',
        'question',
        'explanation',
        'points',
        'order',
    ];

    protected $casts = [
        'points' => 'integer',
        'order'  => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function correctOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->where('is_correct', true);
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst($this->type);
    }
}
