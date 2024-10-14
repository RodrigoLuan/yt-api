<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register()
    {
        $response = $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'message',
            'user',
            'access_token',
        ]);

        $token = $response->json('access_token');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        $this->assertNotEmpty($token);
    }
}
