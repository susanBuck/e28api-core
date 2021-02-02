<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\Product;
use App\Models\GeneratedModels\Favorite;
use App\Models\User;

class ResourceTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     *
     */
    public function testGetProduct()
    {
        $product = Product::factory()->create();
        $r = $this->get('/product/' . $product->id);

        $r->assertJsonPath('success', true);
        $r->assertJsonPath('product.sku', $product->sku);
    }

    /**
     *
     */
    public function testProductNotFound()
    {
        $r = $this->get('/product/999');

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'product-not-found');
    }

    /**
     *
     */
    public function testGetAllProducts()
    {
        $products = Product::factory()->count(10)->create();

        $r = $this->get('/product');

        $r->assertJsonPath('success', true);
        $r->assertJsonCount(10, 'product');
    }

    /**
     *
     */
    public function testGetAllProductsWhenThereAreNoProducts()
    {
        $r = $this->get('/product');
        $r->assertJsonPath('success', true);
        $r->assertJsonCount(0, 'product');
    }

    /**
     *
     */
    public function testSuccessfullyAddingProduct()
    {
        # Using `make` won't actually persist this product
        $product = Product::factory()->make();

        $r = $this->json('POST', '/product', $product->toArray());

        $r->assertJsonPath('success', true);
        $r->assertJsonPath('test', 'product-created');
        $r->assertJsonPath('product.sku', $product->sku);
    }

    /**
     *
     */
    public function testUniqueValidator()
    {
        # Create a product so we can cause a "sku already exists" validation failure
        $product = Product::factory()->create();
        
        $r = $this->json('POST', '/product', $product->toArray());
        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'failed-validation');
        $this->assertNotNull(json_decode($r->content())->errors->sku[0]);
    }

    /**
     *
     */
    public function testValidators()
    {
        # Create a product
        $product = Product::factory()->create();

        # Test `unique:sku` validator first
        $r = $this->json('POST', '/product', $product->toArray());
        $errors = json_decode($r->content())->errors;
        $r->assertSee('The sku has already been taken.');

        # Test all other validators
        $product->name = ''; # required
        $product->sku = '@'.$this->faker->text(200); # alpha_num, max:100
        $product->description = $this->faker->text(50); # min:100
        $product->price = 'One dollar'; # numeric
        $product->perishable = 'false'; # boolean - but this will pass because it will cast to `1`

        $r = $this->json('POST', '/product', $product->toArray());
        $errors = json_decode($r->content())->errors;

        $r->assertSee('The name field is required.');
        $r->assertSee('The sku may not be greater than 100 characters.');
        $r->assertSee('The sku may only contain letters, numbers, dashes and underscores.');
        $r->assertSee('The description must be at least 100 characters.');
        $r->assertSee('The price must be a number.');
        $r->assertDontSee('perishable');
    }
    
    /**
     *
     */
    public function testDeletingProduct()
    {
        $product = Product::factory()->create();

        $r = $this->json("DELETE", '/product/'.$product->id);
        $r->assertJsonPath('success', true);
    }

    /**
     *
     */
    public function testDeletingProductThatDoesNotExist()
    {
        $r = $this->json("DELETE", '/product/999');
        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'product-not-found');
    }

    /**
     *
     */
    public function testUpdatingProduct()
    {
        $product = Product::factory()->create();

        $r = $this->json("PUT", '/product/'.$product->id, $product->toArray());
        $r->assertJsonPath('success', true);
        $r->assertJsonPath('test', 'update-completed');
    }


    /**
     *
     */
    public function testUpdatingProductThatDoesNotExist()
    {
        $product = Product::factory()->create();

        $r = $this->json("PUT", '/product/999', $product->toArray());

        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'update-failed-because-product-not-found');
    }
}