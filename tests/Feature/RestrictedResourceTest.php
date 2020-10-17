<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\Product;
use App\Models\GeneratedModels\Favorite;
use App\Models\User;

class RestrictedResourceTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     *
     */
    public function testItOnlyShowsYouYourFavorites()
    {
        # Setup
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $favorite1 = Favorite::factory()->create(['user_id' => $userA->id]);
        $favorite2 = Favorite::factory()->create(['user_id' => $userB->id]);

        # Act
        $r = $this->actingAs($userA);
        $r = $this->get('/favorite');

        # Assert
        $r->assertJsonCount(1, 'favorite'); # We should see 1, not 2 favorites
    }
    
    /**
     *
     */
    public function testGetFavoriteAsNonOwner()
    {
        $favorite = Favorite::factory()->create();

        # Log in as a user that does not "own" this favorite
        $r = $this->actingAs(User::factory()->create());

        $r = $this->get('/favorite/'.$favorite->id);

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'data-access-denied');
    }

    /**
     *
     */
    public function testGetFavoriteAsOwner()
    {
        $user = User::factory()->create();

        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $r = $this->actingAs($user);

        $r = $this->get('/favorite/'.$favorite->id);

        $r->assertJsonPath('success', true);
        $r->assertJsonPath('favorite.id', $favorite->id);
    }

    /**
     *
     * If a resource is user restricted, a logged in user should not be able to
     * add a resource for another user.
     * This is unlikely to happen via the interface, but it should still be checked
     */
    public function testYouCantAddFavoriteForSomeoneElse()
    {
        $product = Product::factory()->create();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $r = $this->actingAs($userA);

        $r = $this->json('POST', '/favorite', [
            'user_id' => $userB->id,
            'product_id' => $product->id,
        ]);

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'action-unauthorized');
    }

    /**
     *
     */
    public function testYouCantDeleteFavoriteAsNonOwner()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $favorite = Favorite::factory()->create(['user_id' => $userB->id]);
       
        $r = $this->actingAs($userA);

        $r = $this->json('DELETE', '/favorite/'.$favorite->id);

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'data-access-denied');
    }
}