<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\Product;
use App\Models\GeneratedModels\Favorite;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

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
    public function testYouCanViewAFavoriteThatIsYours()
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
     */
    public function testYouCantViewAFavoriteThatIsNotYours()
    {
        $favorite = Favorite::factory()->create(); # This will generate it's own user
        $user = User::factory()->create(); # This will be the user we log in as

        $r = $this->actingAs($user);
        $r = $this->get('/favorite/'.$favorite->id);

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'data-access-denied');
    }

    /**
     *
     */
    public function testYouCantAddAFavoriteForSomeoneElse()
    {
        $product = Product::factory()->create();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $r = $this->actingAs($userA);

        # Here we'll attempt to add a favorite, and specify the user_id for a userB
        # even though we're logged in as userA
        # Rather than fail, it will just overwrite the user_id we specified with our actual user_id
        $r = $this->json('POST', '/favorite', [
            'user_id' => $userB->id,
            'product_id' => $product->id,
        ]);

        $r->assertJsonPath('success', true);
        $r->assertJsonPath('favorite.user_id', $userA->id);
    }

    /**
     *
     */
    public function testNewFavoritesAreAssociatedWithAuthenticatedUser()
    {
        # Setup
        $product = Product::factory()->create();
        $user = User::factory()->create();

        # Act
        $r = $this->actingAs($user);
        $r = $this->json('POST', '/favorite', [
            'product_id' => $product->id,
        ]);

        # Assert
        $favorite = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertTrue($favorite->user_id == $user->id);
        $r->assertJsonPath('success', true);
    }

    /**
     *
     */
    public function testYouCantDeleteFavoriteThatIsNotYours()
    {
        # Setup
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $userB->id]);
        
        # Act
        $r = $this->actingAs($userA);
        $r = $this->json('DELETE', '/favorite/'.$favorite->id);

        # Assert
        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'data-access-denied');
    }
}