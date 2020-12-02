<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
    *
    */
    public function testSuccesfulRegistration()
    {
        # make will create the user but not persist it
        $user = User::factory()->make();

        $r = $this->json('POST', '/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $this->faker->password(8)
        ]);

        $r->assertStatus(200);
        $r->assertJsonPath('success', true);
        $r->assertJsonPath('user.name', $user->name);
        $r->assertJsonPath('user.email', $user->email);
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
        $r->assertJsonPath('success', true);
        $r->assertJsonPath('user.email', $user->email);
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

    /**
     *
     */
    public function testNonLoggedInUserIsNotAuthed()
    {
        $r = $this->json('POST', '/auth');
        
        $r->assertStatus(200);
        $r->assertJson([
            'success' => true,
            'authenticated' => false,
            'user' => null
        ]);
    }

    /**
     *
     */
    public function testLoggedInUserIsAuthed()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $r = $this->json('POST', '/auth');

        $r->assertStatus(200);

        $r->assertJson([
            'success' => true,
            'authenticated' => true,
        ]);

        $r->assertJsonPath('user.email', $user->email);
    }

    /**
     *
     */
    public function testSuccessfulLogout()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $r = $this->json('POST', '/logout');

        $r->assertStatus(200);

        $r->assertJson([
            'success' => true,
            'authenticated' => false,
        ]);
    }
}
