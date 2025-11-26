<?php

namespace Database\Factories;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class IntegrationFactory extends Factory
{
    protected $model = Integration::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['hubspot', 'gmail']),
            'provider_user_id' => (string) fake()->randomNumber(8),
            'provider_email' => fake()->email(),
            'access_token' => Str::random(64),
            'refresh_token' => Str::random(64),
            'token_expires_at' => now()->addHour(),
            'scopes' => ['contacts', 'deals', 'engagements'],
            'metadata' => [],
            'is_active' => true,
            'last_synced_at' => now()->subMinutes(5),
        ];
    }

    public function hubspot(): static
    {
        return $this->state(fn(array $attributes) => [
            'provider' => 'hubspot',
            'scopes' => ['contacts', 'deals', 'engagements', 'timeline'],
        ]);
    }

    public function gmail(): static
    {
        return $this->state(fn(array $attributes) => [
            'provider' => 'gmail',
            'scopes' => ['gmail.readonly', 'gmail.send'],
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'token_expires_at' => now()->subHour(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
