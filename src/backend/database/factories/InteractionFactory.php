<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interaction>
 */
class InteractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['email', 'call', 'meeting', 'note']),
            'direction' => fake()->randomElement(['inbound', 'outbound', null]),
            'subject' => fake()->sentence(),
            'content' => fake()->paragraphs(2, true),
            'summary' => fake()->sentence(),
            'sentiment' => fake()->randomElement(['positive', 'neutral', 'negative', null]),
            'duration_minutes' => fake()->optional()->numberBetween(5, 120),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function call(): static
    {
        return $this->state(fn () => [
            'type' => 'call',
            'direction' => fake()->randomElement(['inbound', 'outbound']),
        ]);
    }

    public function email(): static
    {
        return $this->state(fn () => [
            'type' => 'email',
            'direction' => fake()->randomElement(['inbound', 'outbound']),
        ]);
    }

    public function note(): static
    {
        return $this->state(fn () => [
            'type' => 'note',
            'direction' => null,
        ]);
    }
}
