<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_user_can_list_leads(): void
    {
        Lead::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_create_lead(): void
    {
        $leadData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company' => 'Acme Inc',
            'status' => 'new',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/leads', $leadData);

        $response->assertStatus(201)
            ->assertJsonPath('first_name', 'John');

        $this->assertDatabaseHas('leads', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_can_view_lead(): void
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $lead->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $lead->id);
    }

    public function test_user_can_update_lead(): void
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/leads/' . $lead->id, [
                'first_name' => 'Updated Name',
                'status' => 'qualified',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'first_name' => 'Updated Name',
            'status' => 'qualified',
        ]);
    }

    public function test_user_can_delete_lead(): void
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/leads/' . $lead->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_user_cannot_access_other_users_leads(): void
    {
        $otherUser = User::factory()->create();
        $lead = Lead::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $lead->id);

        $response->assertStatus(403);
    }

    public function test_lead_requires_first_name(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/leads', [
                'email' => 'test@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }
}
