<?php

namespace Tests\Feature;

use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResearchProjectAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_api_requires_authentication(): void
    {
        $this->getJson('/api/research/projects')->assertUnauthorized();
    }

    public function test_user_can_create_and_list_only_owned_and_public_demo_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        ResearchProject::query()->create([
            'owner_id' => $otherUser->id,
            'name' => 'Other private project',
            'slug' => 'other-private-project',
            'status' => 'draft',
        ]);
        ResearchProject::query()->create([
            'name' => 'Public synthetic demo',
            'slug' => 'public-synthetic-demo',
            'status' => 'demo',
        ]);
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/research/projects', [
            'name' => 'My study',
            'slug' => 'my-study',
            'description' => 'Synthetic research project',
        ])->assertCreated()->assertJsonPath('data.owner_id', $user->id);

        $this->getJson('/api/research/projects')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['slug' => 'other-private-project']);

        $this->getJson('/api/research/projects/'.$created->json('data.id'))->assertOk();
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = ResearchProject::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Private study',
            'slug' => 'private-study',
            'status' => 'draft',
        ]);
        Sanctum::actingAs($otherUser);

        $this->getJson('/api/research/projects/'.$project->id)->assertForbidden();
    }
}
