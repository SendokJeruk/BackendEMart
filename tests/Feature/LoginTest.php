<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
     protected $LoginEndpoint = '/api/auth/login';
    /**
     * A basic feature test example.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->postJson($this->LoginEndpoint, [
            'email' => 'user@test.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'data' => ['access_token']
                ]);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        $response = $this->postJson($this->LoginEndpoint, [
            'email' => 'user@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson($this->LoginEndpoint, [
            'email' => 'invalid',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }
}
