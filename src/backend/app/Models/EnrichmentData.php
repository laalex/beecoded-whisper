<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrichmentData extends Model
{
    use HasFactory;

    protected $table = 'enrichment_data';

    protected $fillable = [
        'lead_id',
        'provider',
        'company_data',
        'contact_data',
        'social_profiles',
        'technologies',
        'funding_data',
        'employee_count',
        'industry',
        'annual_revenue',
        'enriched_at',
        'hubspot_lifecycle_stage',
        'hubspot_deals',
        'hubspot_activities',
        'hubspot_owner',
        'last_synced_at',
        'sync_error',
    ];

    protected function casts(): array
    {
        return [
            'company_data' => 'array',
            'contact_data' => 'array',
            'social_profiles' => 'array',
            'technologies' => 'array',
            'funding_data' => 'array',
            'annual_revenue' => 'decimal:2',
            'enriched_at' => 'datetime',
            'hubspot_deals' => 'array',
            'hubspot_activities' => 'array',
            'hubspot_owner' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function isStale(int $minutes = 30): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->last_synced_at->diffInMinutes(now()) > $minutes;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
