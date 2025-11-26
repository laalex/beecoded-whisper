<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reminder>
 */
class ReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lead_id' => Lead::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(['followup', 'task', 'meeting', 'call', 'email']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_at' => fake()->dateTimeBetween('now', '+14 days'),
            'completed_at' => null,
            'is_ai_generated' => fake()->boolean(20),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_at' => fake()->dateTimeBetween('-7 days', '-1 day'),
            'completed_at' => null,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => 'urgent']);
    }
}
