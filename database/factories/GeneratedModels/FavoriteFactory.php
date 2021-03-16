<?php

/*
NOTE: This Factory exists only for feature tests
It is not actually generated -
it only exists in a directory with the name `GeneratedModels`
because there’s a correlation betwen model and factory paths

e.g. :
Model at /app/Models/GeneratedModels/Product.php
Expects a factory at /database/Factories/GeneratedModels/ProductFactory.php
*/

namespace Database\Factories\GeneratedModels;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GeneratedModels\Favorite;
use App\Models\GeneratedModels\Product;
use App\Models\User;

class FavoriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory()
        ];
    }
}
