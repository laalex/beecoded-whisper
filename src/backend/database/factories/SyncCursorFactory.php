<?php

namespace Database\Factories;

use App\Models\Integration;
use App\Models\SyncCursor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyncCursorFactory extends Factory
{
    protected $model = SyncCursor::class;

    public function definition(): array
    {
        return [
            'integration_id' => Integration::factory(),
            'cursor_type' => 'contacts',
            'last_sync_at' => null,
            'cursor_value' => null,
            'records_synced' => 0,
            'metadata' => null,
        ];
    }

    public function withLastSync(): self
    {
        return $this->state(fn () => [
            'last_sync_at' => now()->subHour(),
        ]);
    }
}
