<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ai_analyses';

    protected $fillable = [
        'lead_id',
        'analysis_type',
        'insights',
        'recommendations',
        'risks',
        'opportunities',
        'confidence_score',
        'model_used',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'insights' => 'array',
            'recommendations' => 'array',
            'risks' => 'array',
            'opportunities' => 'array',
            'confidence_score' => 'decimal:2',
            'analyzed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isStale(int $minutes = 15): bool
    {
        if (!$this->analyzed_at) {
            return true;
        }

        return $this->analyzed_at->diffInMinutes(now()) > $minutes;
    }
}
