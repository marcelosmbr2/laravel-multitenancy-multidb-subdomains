<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Only landlord seeders are listed here. `CompanySeeder` provisions each tenant database and
     * calls `TenantDataSeeder` inside the tenant context.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            AdminSeeder::class,
            CompanySeeder::class,
        ]);
    }
}
