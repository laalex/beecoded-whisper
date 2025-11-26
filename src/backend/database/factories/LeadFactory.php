<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'website' => fake()->url(),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'proposal', 'negotiation']),
            'source' => fake()->randomElement(['manual', 'hubspot', 'gmail', 'website']),
            'estimated_value' => fake()->randomFloat(2, 1000, 100000),
            'score' => fake()->numberBetween(0, 100),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function new(): static
    {
        return $this->state(fn () => ['status' => 'new']);
    }

    public function qualified(): static
    {
        return $this->state(fn () => ['status' => 'qualified']);
    }

    public function won(): static
    {
        return $this->state(fn () => ['status' => 'won']);
    }
}
