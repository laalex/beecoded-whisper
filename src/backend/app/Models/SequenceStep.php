<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_id',
        'order',
        'type',
        'name',
        'content',
        'subject',
        'delay_days',
        'delay_hours',
        'conditions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }
}
