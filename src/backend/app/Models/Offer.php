<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'user_id',
        'title',
        'description',
        'amount',
        'currency',
        'status',
        'line_items',
        'terms',
        'notes',
        'valid_until',
        'sent_at',
        'viewed_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'line_items' => 'array',
            'terms' => 'array',
            'valid_until' => 'datetime',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'responded_at' => 'datetime',
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
}
