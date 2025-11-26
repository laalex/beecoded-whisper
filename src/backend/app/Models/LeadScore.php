<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'total_score',
        'engagement_score',
        'fit_score',
        'behavior_score',
        'recency_score',
        'score_breakdown',
        'factors',
        'conversion_probability',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'score_breakdown' => 'array',
            'factors' => 'array',
            'conversion_probability' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
