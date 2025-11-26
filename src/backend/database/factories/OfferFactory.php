<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'currency' => 'USD',
            'status' => fake()->randomElement(['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired']),
            'valid_until' => fake()->dateTimeBetween('+7 days', '+30 days'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
            'responded_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }
}
