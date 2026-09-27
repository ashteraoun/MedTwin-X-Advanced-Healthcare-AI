<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_researcher_can_register_with_a_confirmed_strong_password(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Researcher',
            'email' => 'researcher@example.test',
            'password' => 'synthetic-research-passphrase',
            'password_confirmation' => 'synthetic-research-passphrase',
        ])->assertCreated()->assertJsonPath('user.email', 'researcher@example.test');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_rejects_a_short_password(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Researcher',
            'email' => 'researcher@example.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_can_issue_and_revoke_a_sanctum_token(): void
    {
        User::factory()->create([
            'email' => 'researcher@example.test',
            'password' => Hash::make('fixture-password'),
        ]);

        $response = $this->postJson('/api/auth/token', [
            'email' => 'researcher@example.test',
            'password' => 'fixture-password',
        ])->assertOk()->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->withToken($response->json('token'))
            ->postJson('/api/auth/logout')
            ->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_credentials_do_not_issue_a_token(): void
    {
        User::factory()->create([
            'email' => 'researcher@example.test',
            'password' => Hash::make('fixture-password'),
        ]);

        $this->postJson('/api/auth/token', [
            'email' => 'researcher@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
