<?php

namespace Tests\Feature;

use Config;
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
    public function setUp() :void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->user = User::factory()->create();
        $this->guest = new \stdClass();
    }

    /**
     *
     */
    private function isGuest($user)
    {
        return get_class($user) != 'App\Models\User';
    }
    
    /**
     *
     */
    public function canIndexOwn($permitted, $user)
    {
        if ($this->isGuest($user)) {
            dd('Guests can not attempt to index own, because guests can’t have “own”');
        }

        $this->actingAs($user);
        
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $r = $this->get('/favorite');

        if ($permitted) {
            $r->assertJsonCount(1, 'favorite');
        } else {
            $r->assertJsonCount(0, 'favorite');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canIndexOthers($permitted, $user)
    {
        if (!$this->isGuest($user)) {
            $this->actingAs($user);
        }

        $favorite = Favorite::factory()->create();

        $r = $this->get('/favorite');

        if ($permitted) {
            $r->assertJsonCount(1, 'favorite');
        } else {
            # Non permitted guests see access denied
            if ($this->isGuest($user)) {
                $r->assertJsonPath('test', 'access-denied');
            # Users just won't see any favorites
            } else {
                $r->assertJsonCount(0, 'favorite');
            }
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canQueryOwn($permitted, $user)
    {
        if ($this->isGuest($user)) {
            dd('Guests can not attempt to query own, because guests can’t have “own”');
        }

        $this->actingAs($user);

        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $r = $this->get('/favorite/query?id='.$favorite->id);

        if ($permitted) {
            $r->assertJsonCount(1, 'favorite');
        } else {
            $r->assertJsonCount(0, 'favorite');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canQueryOthers($permitted, $user)
    {
        if (!$this->isGuest($user)) {
            $this->actingAs($user);
        }

        $favorite = Favorite::factory()->create();

        $r = $this->get('/favorite/query?id=' . $favorite->id);

        if ($permitted) {
            $r->assertJsonCount(1, 'favorite');
        } else {
            # Non permitted guests see access denied
            if ($this->isGuest($user)) {
                $r->assertJsonPath('test', 'access-denied');
            # Users just won’t see any favorites
            } else {
                $r->assertJsonCount(0, 'favorite');
            }
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canShowOwn($permitted, $user)
    {
        if ($this->isGuest($user)) {
            dd('Guests can not attempt to show own, because guests can’t have “own”');
        }
        
        $this->actingAs($user);
        
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $r = $this->get('/favorite/'.$favorite->id);
        
        if ($permitted) {
            $r->assertJsonPath('favorite.id', $favorite->id);
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canShowOthers($permitted, $user)
    {
        $favorite = Favorite::factory()->create();

        if (!$this->isGuest($user)) {
            $this->actingAs($user);
        }
        
        $r = $this->get('/favorite/' . $favorite->id);

        if ($permitted) {
            $r->assertJsonPath('favorite.id', $favorite->id);
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canUpdateOwn($permitted, $user)
    {
        if ($this->isGuest($user)) {
            dd('Guests can not attempt to update own, because guests can’t have “own”');
        }

        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
       
        # update
        $r = $this->put('/favorite/' . $favorite->id, ['user_id' => $user->id, 'product_id' => $favorite->product_id]);
        if ($permitted) {
            $r->assertJsonPath('test', 'favorite-updated');
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canUpdateOthers($permitted, $user)
    {
        $favorite = Favorite::factory()->create();
        $otherUser = User::factory()->create();

        if (!$this->isGuest($user)) {
            $this->actingAs($user);
        }
        
        # update
        $r = $this->put('/favorite/' . $favorite->id, ['user_id' => $otherUser->id, 'product_id' => $favorite->product_id]);
        if ($permitted) {
            $r->assertJsonPath('test', 'favorite-updated');
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }

        $favorite->delete();
    }

    /**
     *
     */
    public function canDeleteOwn($permitted, $user)
    {
        if ($this->isGuest($user)) {
            dd('Guests can not attempt to delete own, because guests can’t have “own”');
        }

        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
       
        # delete
        $r = $this->delete('/favorite/' . $favorite->id);
        if ($permitted) {
            $r->assertJsonPath('test', 'favorite-deleted');
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }
    }

    /**
     *
     */
    public function canDeleteOthers($permitted, $user)
    {
        $favorite = Favorite::factory()->create();
        $otherUser = User::factory()->create();

        if (!$this->isGuest($user)) {
            $this->actingAs($user);
        }
        
        # delete
        $r = $this->delete('/favorite/' . $favorite->id);
        if ($permitted) {
            $r->assertJsonPath('test', 'favorite-deleted');
        } else {
            $r->assertJsonPath('test', 'access-denied');
        }
    }


    /**
     * GUEST TESTS
     */
    public function testLevel0AsGuest()
    {
        Config::set('permissions.favorite', 0);
        $this->canIndexOthers(true, $this->guest);
        $this->canShowOthers(true, $this->guest);
        $this->canQueryOthers(true, $this->guest);
        $this->canUpdateOthers(true, $this->guest);
        $this->canDeleteOthers(true, $this->guest);
    }

    public function testLevel1AsGuest()
    {
        # 1 - Resource is readable by all, but only users can alter
        Config::set('permissions.favorite', 1);
        $this->canIndexOthers(true, $this->guest);
        $this->canShowOthers(true, $this->guest);
        $this->canQueryOthers(true, $this->guest);
        $this->canUpdateOthers(false, $this->guest);
        $this->canDeleteOthers(false, $this->guest);
    }

    public function testLevel2AsGuest()
    {
        # 2 - Resource is readable by all, but only owners can alter
        Config::set('permissions.favorite', 2);
        $this->canIndexOthers(true, $this->guest);
        $this->canShowOthers(true, $this->guest);
        $this->canQueryOthers(true, $this->guest);
        $this->canUpdateOthers(false, $this->guest);
        $this->canDeleteOthers(false, $this->guest);
    }

    public function testLevel3AsGuest()
    {
        # 3 - Resource is only readable after login; users can alter
        Config::set('permissions.favorite', 3);
        $this->canIndexOthers(false, $this->guest);
        $this->canShowOthers(false, $this->guest);
        $this->canQueryOthers(false, $this->guest);
        $this->canUpdateOthers(false, $this->guest);
        $this->canDeleteOthers(false, $this->guest);
    }

    public function testLevel4AsGuest()
    {
        # 4 - Resource is only readable after login; only owner can alter
        Config::set('permissions.favorite', 4);
        $this->canIndexOthers(false, $this->guest);
        $this->canShowOthers(false, $this->guest);
        $this->canQueryOthers(false, $this->guest);
        $this->canUpdateOthers(false, $this->guest);
        $this->canDeleteOthers(false, $this->guest);
    }

    public function testLevel5AsGuest()
    {
        # 5 - Resource is only readable/alterable by owner
        Config::set('permissions.favorite', 5);
        $this->canIndexOthers(false, $this->guest);
        $this->canShowOthers(false, $this->guest);
        $this->canQueryOthers(false, $this->guest);
        $this->canUpdateOthers(false, $this->guest);
        $this->canDeleteOthers(false, $this->guest);
    }


    
    /**
     * USER TESTS
     */
    public function testLevel0AsUser()
    {
        # 0 - Resource is readable and alterable by all
        Config::set('permissions.favorite', 0);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(true, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(true, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(true, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(true, $this->user);
        $this->canDeleteOthers(true, $this->user);
    }
   
    public function testLevel1AsUser()
    {
        // # 1 - Resource is readable by all, but only users can alter
        Config::set('permissions.favorite', 1);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(true, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(true, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(true, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(true, $this->user);
        $this->canDeleteOthers(true, $this->user);
    }

    public function testLevel2AsUser()
    {
        # 2 - Resource is readable by all, but only owners can alter
        Config::set('permissions.favorite', 2);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(true, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(true, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(true, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(false, $this->user);
        $this->canDeleteOwn(true, $this->user);
        $this->canDeleteOthers(false, $this->user);
    }

    public function testLevel3AsUser()
    {
        # 3 - Resource is only readable after login; users can alter
        Config::set('permissions.favorite', 3);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(true, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(true, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(true, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(true, $this->user);
        $this->canDeleteOthers(true, $this->user);
    }

    public function testLevel4AsUser()
    {
        # 4 - Resource is only readable after login; only owner can alter
        Config::set('permissions.favorite', 4);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(true, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(true, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(true, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(false, $this->user);
        $this->canDeleteOthers(false, $this->user);
    }

    public function testLevel5AsUser()
    {
        # 5 - Resource is only readable/alterable by owner
        Config::set('permissions.favorite', 5);
        $this->canIndexOwn(true, $this->user);
        $this->canIndexOthers(false, $this->user);
        $this->canShowOwn(true, $this->user);
        $this->canShowOthers(false, $this->user);
        $this->canQueryOwn(true, $this->user);
        $this->canQueryOthers(false, $this->user);
        $this->canUpdateOwn(true, $this->user);
        $this->canUpdateOthers(false, $this->user);
        $this->canDeleteOwn(true, $this->owner);
        $this->canDeleteOthers(false, $this->user);
    }
}
