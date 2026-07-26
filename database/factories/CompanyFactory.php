<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->company());

        return [
            'plan_id' => Plan::factory(),
            'name' => Str::headline($slug),
            'slug' => $slug,
            'database' => $slug,
            'document' => fake()->numerify('##.###.###/####-##'),
            'phone' => fake()->numerify('+55 11 9####-####'),
        ];
    }

    /**
     * Give the company a specific name, deriving the slug and the database from it.
     */
    public function named(string $name): static
    {
        return $this->state(fn (): array => [
            'name' => $name,
            'slug' => Str::slug($name),
            'database' => Str::slug($name),
        ]);
    }
}
