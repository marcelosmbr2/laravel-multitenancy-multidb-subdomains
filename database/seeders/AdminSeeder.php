<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The global administrator: no company, therefore no current tenant of their own.
 */
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@app.com'], [
            'company_id' => null,
            'name' => 'Global Admin',
            'role' => UserRole::Admin,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
