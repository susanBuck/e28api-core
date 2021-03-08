<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\Product;
use App\Models\GeneratedModels\Favorite;
use App\Models\User;

class RefreshTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    # https://github.com/laravel/framework/issues/26450#issuecomment-449401202
    public $mockConsoleOutput = false;

    /**
     *
     */
    public function testRefresh()
    {
        # Because of RefreshDatabase, all tests start with a blank database
        # this just confirms that
        $this->assertTrue(Product::all()->count() == 0);
   
        # Invoke the /refresh route we’re testing
        $r = $this->get('/refresh');

        # Confirm output
        $r->assertJsonPath('success', true);
        $r->assertJsonPath('message', 'Tables were cleared and re-seeded.');

        # And most importantly, confirm we now have 10 seeded products
        $this->assertTrue(Product::all()->count() == 10);

        # Add a product
        Product::factory()->create();

        # We should now have 11
        $this->assertTrue(Product::all()->count() == 11);

        # One more refresh
        $r = $this->get('/refresh');
        $r->assertJsonPath('success', true);
        $r->assertJsonPath('message', 'Tables were cleared and re-seeded.');

        # We have 10 again
        $this->assertTrue(Product::all()->count() == 10);
    }
}