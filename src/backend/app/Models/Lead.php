<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'external_id',
        'source',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'website',
        'linkedin_url',
        'status',
        'estimated_value',
        'score',
        'tags',
        'custom_fields',
        'notes',
        'last_contacted_at',
        'next_followup_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'custom_fields' => 'array',
            'estimated_value' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'next_followup_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function transcriptions(): HasMany
    {
        return $this->hasMany(Transcription::class);
    }

    public function enrichmentData(): HasOne
    {
        return $this->hasOne(EnrichmentData::class);
    }

    public function scoreDetails(): HasOne
    {
        return $this->hasOne(LeadScore::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
