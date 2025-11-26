<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'title',
        'description',
        'type',
        'priority',
        'due_at',
        'completed_at',
        'is_ai_generated',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_ai_generated' => 'boolean',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function isOverdue(): bool
    {
        return !$this->completed_at && $this->due_at->isPast();
    }
}
