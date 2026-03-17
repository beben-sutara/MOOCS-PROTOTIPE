<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserXpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'source',
        'previous_xp',
        'current_xp',
        'previous_level',
        'current_level',
        'leveled_up',
        'metadata',
    ];

    protected $casts = [
        'leveled_up' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
