<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => Str::upper(fake()->unique()->bothify('???-####')),
            'name' => Str::headline(fake()->unique()->words(2, true)),
            'price' => fake()->randomFloat(2, 5, 900),
            'quantity' => fake()->numberBetween(0, 250),
        ];
    }
}
