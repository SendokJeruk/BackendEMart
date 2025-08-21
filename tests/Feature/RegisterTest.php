<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public string $RegisterEndpoint = '/api/auth/register';

    /** @test */
    public function user_can_register_with_valid_credentials()
    {
        $response = $this->postJson($this->RegisterEndpoint, [
            'name' => 'muzan',
            'email' => 'muzan@gmail.com',
            'no_telp' => '081229987562',
            'password' => 'BudionoSiregar1!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Registrasi Berhasil',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'no_telp',
                    'role_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_register_with_weak_password()
    {
        $response = $this->postJson($this->RegisterEndpoint, [
            'name' => 'muzan',
            'email' => 'muzanweak@gmail.com',
            'no_telp' => '081111111111',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Data',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function user_cannot_register_with_duplicate_email()
    {
        // register pertama
        $this->postJson($this->RegisterEndpoint, [
            'name' => 'muzan',
            'email' => 'muzan@gmail.com',
            'no_telp' => '081229987562',
            'password' => 'BudionoSiregar1!',
        ]);

        $response = $this->postJson($this->RegisterEndpoint, [
            'name' => 'muzan2',
            'email' => 'muzan@gmail.com',
            'no_telp' => '081222222222',
            'password' => 'BudionoSiregar1!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
