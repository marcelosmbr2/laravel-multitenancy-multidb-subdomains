<?php

namespace Database\Seeders;

use App\Actions\Tenancy\CreateTenantDatabase;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the two example tenants, each with a SQLite database of its own.
 */
class CompanySeeder extends Seeder
{
    /**
     * @var list<array{name: string, slug: string, plan: string, employees: int}>
     */
    private const COMPANIES = [
        ['name' => 'Acme', 'slug' => 'acme', 'plan' => 'basic', 'employees' => 3],
        ['name' => 'Globex', 'slug' => 'globex', 'plan' => 'premium', 'employees' => 4],
    ];

    public function __construct(private CreateTenantDatabase $createTenantDatabase) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::COMPANIES as $definition) {
            $company = Company::updateOrCreate(['slug' => $definition['slug']], [
                'plan_id' => Plan::where('slug', $definition['plan'])->value('id'),
                'name' => $definition['name'],
                'database' => $definition['slug'],
                'document' => fake()->numerify('##.###.###/####-##'),
                'phone' => fake()->numerify('+55 11 9####-####'),
            ]);

            // Recreates the SQLite file from scratch and runs the tenant migrations into it.
            $this->createTenantDatabase->execute($company, fresh: true);

            $this->seedUsers($company, $definition['employees']);

            $company->execute(fn () => $this->call(TenantDataSeeder::class));

            $this->command?->info("Seeded {$company->name} ({$company->database}.sqlite)");
        }
    }

    private function seedUsers(Company $company, int $employees): void
    {
        User::updateOrCreate(['email' => "owner@{$company->slug}.test"], [
            'company_id' => $company->id,
            'name' => "{$company->name} Owner",
            'role' => UserRole::Company,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        foreach (range(1, $employees) as $index) {
            User::updateOrCreate(['email' => "employee{$index}@{$company->slug}.test"], [
                'company_id' => $company->id,
                'name' => "{$company->name} Employee {$index}",
                'role' => UserRole::Employee,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
