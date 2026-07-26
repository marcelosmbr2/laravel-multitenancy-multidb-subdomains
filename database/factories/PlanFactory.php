<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'max_employees' => 5,
            'max_products' => 20,
        ];
    }

    public function basic(): static
    {
        return $this->state([
            'name' => 'Basic',
            'slug' => 'basic',
            'max_employees' => 5,
            'max_products' => 20,
        ]);
    }

    public function premium(): static
    {
        return $this->state([
            'name' => 'Premium',
            'slug' => 'premium',
            'max_employees' => 50,
            'max_products' => 500,
        ]);
    }
}
