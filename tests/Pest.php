<?php

use App\Actions\Tenancy\CreateTenantDatabase;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Tenant databases
|--------------------------------------------------------------------------
|
| `RefreshDatabase` only rolls back the landlord connection. Each tenant is a real SQLite file
| under the directory configured by TENANT_DATABASE_DIRECTORY in phpunit.xml, so those files have
| to be removed between tests by hand.
|
*/

afterEach(function () {
    Company::forgetCurrent();

    File::deleteDirectory(base_path(config('multitenancy.tenant_database_directory')));
});

/**
 * Create a company with a plan and a freshly migrated database of its own.
 */
function createCompany(string $name = 'Acme', string $planSlug = 'basic'): Company
{
    $plan = Plan::firstWhere('slug', $planSlug) ?? Plan::factory()->{$planSlug}()->create();

    $company = Company::factory()->named($name)->create(['plan_id' => $plan->id]);

    app(CreateTenantDatabase::class)->execute($company, fresh: true);

    return $company;
}

/*
|--------------------------------------------------------------------------
| Hosts
|--------------------------------------------------------------------------
|
| The tenant is identified by the subdomain, so a request has to name the host it is meant for.
| These wrap the two addresses a test can aim at: a company's own subdomain, and the bare domain
| that carries registration, password recovery and the administration panel.
|
*/

/**
 * An absolute URL inside the subdomain of the given company.
 *
 * The trailing slash of the root is trimmed so that the result compares equal to what `route()`
 * generates, which is what an `assertRedirect()` is up against.
 */
function tenantUrl(Company $company, string $path = '/'): string
{
    return rtrim($company->url($path), '/');
}

/**
 * An absolute URL on the bare domain.
 */
function centralUrl(string $path = '/'): string
{
    return rtrim(Company::centralUrl($path), '/');
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
