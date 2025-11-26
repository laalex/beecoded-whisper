<?php

namespace Database\Factories;

use App\Models\EnrichmentData;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrichmentDataFactory extends Factory
{
    protected $model = EnrichmentData::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'provider' => 'hubspot',
            'company_data' => [
                'name' => fake()->company(),
                'domain' => fake()->domainName(),
            ],
            'contact_data' => [
                'title' => fake()->jobTitle(),
                'department' => fake()->randomElement(['Sales', 'Marketing', 'Engineering', 'HR']),
            ],
            'social_profiles' => [
                'linkedin' => 'https://linkedin.com/in/' . fake()->userName(),
            ],
            'technologies' => fake()->randomElements(
                ['Salesforce', 'HubSpot', 'Slack', 'Zoom', 'AWS', 'Google Cloud'],
                fake()->numberBetween(1, 4)
            ),
            'funding_data' => null,
            'employee_count' => fake()->randomElement([10, 50, 100, 500, 1000, 5000]),
            'industry' => fake()->randomElement([
                'Technology',
                'Healthcare',
                'Finance',
                'Retail',
                'Manufacturing',
            ]),
            'annual_revenue' => fake()->randomElement([100000, 500000, 1000000, 5000000, 10000000]),
            'enriched_at' => now(),
            'hubspot_lifecycle_stage' => fake()->randomElement([
                'subscriber',
                'lead',
                'marketingqualifiedlead',
                'salesqualifiedlead',
                'opportunity',
                'customer',
            ]),
            'hubspot_deals' => [],
            'hubspot_activities' => [],
            'hubspot_owner' => [
                'id' => (string) fake()->randomNumber(6),
                'email' => fake()->email(),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
            ],
            'last_synced_at' => now(),
            'sync_error' => null,
        ];
    }

    public function withDeals(int $count = 2): static
    {
        return $this->state(fn(array $attributes) => [
            'hubspot_deals' => collect(range(1, $count))->map(fn($i) => [
                'id' => (string) fake()->randomNumber(6),
                'name' => fake()->sentence(3),
                'amount' => fake()->randomFloat(2, 1000, 100000),
                'stage' => fake()->randomElement([
                    'appointmentscheduled',
                    'qualifiedtobuy',
                    'presentationscheduled',
                    'decisionmakerboughtin',
                    'closedwon',
                    'closedlost',
                ]),
                'close_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            ])->toArray(),
        ]);
    }

    public function withActivities(int $count = 5): static
    {
        return $this->state(fn(array $attributes) => [
            'hubspot_activities' => collect(range(1, $count))->map(fn($i) => [
                'id' => (string) fake()->randomNumber(6),
                'type' => fake()->randomElement(['EMAIL', 'CALL', 'MEETING', 'NOTE', 'TASK']),
                'timestamp' => fake()->dateTimeBetween('-6 months', 'now')->format('c'),
                'metadata' => [
                    'subject' => fake()->sentence(),
                    'body' => fake()->paragraph(),
                ],
            ])->toArray(),
        ]);
    }

    public function stale(): static
    {
        return $this->state(fn(array $attributes) => [
            'last_synced_at' => now()->subHours(2),
        ]);
    }
}
