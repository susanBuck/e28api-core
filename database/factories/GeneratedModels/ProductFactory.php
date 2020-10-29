<?php

/*
NOTE: This Factory exists only for feature tests
It is not actually generated -
it only exists in a directory with the name `GeneratedModels`
because there's a corelation betwen model and factory paths

e.g. :
Model at /app/Models/GeneratedModels/Product.php
Expects a factory at /database/Factories/GeneratedModels/ProductFactory.php
*/

namespace Database\Factories\GeneratedModels;

use App\Models\GeneratedModels\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'sku' =>  Str::kebab($name),
            'description' => $this->faker->text(500),
            'price' => $this->faker->randomFloat(2, 5, 10),
            'available' => $this->faker->randomDigit,
            'weight' => $this->faker->randomFloat(2, 5, 10),
            'perishable' => $this->faker->boolean,
        ];
    }
}