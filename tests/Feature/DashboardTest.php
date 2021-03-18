<?php

# /Users/Susan/Sites/hes/e28api/core/tests/Feature/DashboardTest.php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\Product;
use App\Models\GeneratedModels\Favorite;
use App\Models\User;

class DashboardTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     *
     */
    public function testLoadDashboard()
    {
        $r = $this->get('/');
        $r->assertSee("api-heading");
        $r->assertSee("product");
        $r->assertSee("favorite");
        $r->assertSee("user");
    }
}