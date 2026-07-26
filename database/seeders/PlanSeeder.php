<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Plans are global, so they live in the landlord database.
 */
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'basic'], [
            'name' => 'Basic',
            'max_employees' => 5,
            'max_products' => 20,
        ]);

        Plan::updateOrCreate(['slug' => 'premium'], [
            'name' => 'Premium',
            'max_employees' => 50,
            'max_products' => 500,
        ]);
    }
}
