<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GeneratedModels\product;
use App\Models\GeneratedModels\favorite;
use App\Models\User;

class Resourcetest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @group focus
     */
    public function testGetProduct()
    {
        $product = Product::factory()->create();
        $r = $this->get('/product/' . $product->id);

        $r->assertJsonPath('success', true);
        $r->assertJsonPath('product.slug', $product->slug);
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
        $r->assertJsonPath('product.slug', $product->slug);
    }

    /**
     *
     */
    public function testValidationFailureWhenAddingProduct()
    {
        # Create a product so we can cause a "slug already exists" validation failure
        $product = Product::factory()->create();

        $r = $this->json('POST', '/product', $product->toArray());
        $r->assertJsonPath('success', false);
        $r->assertJsonPath('test', 'failed-validation');
        $this->assertNotNull(json_decode($r->content())->errors->slug[0]);
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