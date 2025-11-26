<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncCursor extends Model
{
    use HasFactory;
    protected $fillable = [
        'integration_id',
        'cursor_type',
        'last_sync_at',
        'cursor_value',
        'records_synced',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Get or create a cursor for a specific integration and type.
     */
    public static function getOrCreate(Integration $integration, string $cursorType): self
    {
        return self::firstOrCreate(
            [
                'integration_id' => $integration->id,
                'cursor_type' => $cursorType,
            ],
            [
                'records_synced' => 0,
            ]
        );
    }

    /**
     * Update cursor after successful sync.
     */
    public function updateAfterSync(?string $nextCursor, int $recordCount): void
    {
        $this->update([
            'last_sync_at' => now(),
            'cursor_value' => $nextCursor,
            'records_synced' => $this->records_synced + $recordCount,
        ]);
    }
}
