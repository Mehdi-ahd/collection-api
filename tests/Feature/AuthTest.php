<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_renvoie_l_utilisateur_connecte_avec_un_token_valide(): void
    {
        $user = User::factory()->create();

        $token = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->json('data.token');

        $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_refuse_user_sans_token(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }
}
