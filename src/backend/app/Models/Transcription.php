<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transcription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'interaction_id',
        'type',
        'audio_file_path',
        'transcript',
        'summary',
        'action_items',
        'key_points',
        'duration_seconds',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'action_items' => 'array',
            'key_points' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class);
    }
}
