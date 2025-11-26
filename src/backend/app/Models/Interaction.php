<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'direction',
        'subject',
        'content',
        'summary',
        'metadata',
        'sentiment',
        'duration_minutes',
        'external_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transcription(): HasOne
    {
        return $this->hasOne(Transcription::class);
    }
}
