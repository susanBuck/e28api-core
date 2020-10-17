<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;


    /**
    *
    */
    public function testSuccesfulRegistration()
    {
        $r = $this->json('POST', '/register', [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password(8)
        ]);

        $r->assertStatus(201);
    }

    /**
     *
     */
    public function testDuplicateEmailRegistration()
    {
        $user = User::factory()->create();

        $r = $this->json('POST', '/register', [
            'name' => $this->faker->name,
            'email' => $user->email,
            'password' => $this->faker->password(8)
        ]);

        $r->assertStatus(200);

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'registration-failed');
    }

    /**
     *
     */
    public function testSuccesfulLogin()
    {
        $user = User::factory()->create();

        $r = $this->json('POST', '/login', [
            'email' => $user->email,
            'password' => 'asdfasdf'
        ]);
        
        $r->assertStatus(200);
        $r->assertJson(['success' => true]);
        $r->assertJsonPath('user.email', $user->email);
        $r->assertSeeText('token');
    }

    /**
     *
     */
    public function testFailedLogin()
    {
        $user = User::factory()->create();

        $r = $this->json('POST', '/login', [
            'email' => $user->email,
            'password' => 'invalid password'
        ]);
        
        $r->assertStatus(200);
        $r->assertJson(['success' => false]);
        $r->assertJsonPath('test', 'login-failed-bad-credentials');
    }
}