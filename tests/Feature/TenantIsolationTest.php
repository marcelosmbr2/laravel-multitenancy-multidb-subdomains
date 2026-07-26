<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;

/**
 * Seed one category and one product into the database of the given company.
 */
function seedCatalogue(Company $company, string $sku): void
{
    $company->execute(function () use ($company, $sku): void {
        $category = Category::create(['name' => "{$company->name} Category"]);

        Product::create([
            'category_id' => $category->id,
            'sku' => $sku,
            'name' => "{$company->name} Widget",
            'price' => 10.00,
            'quantity' => 5,
        ]);
    });
}

test('each company reads products from its own database', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    seedCatalogue($acme, 'ACME-1');
    seedCatalogue($globex, 'GLOBEX-1');

    expect($acme->execute(fn () => Product::pluck('sku')->all()))->toBe(['ACME-1'])
        ->and($globex->execute(fn () => Product::pluck('sku')->all()))->toBe(['GLOBEX-1']);
});

test('the current tenant comes from the subdomain', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    expect(Company::checkCurrent())->toBeFalse();

    $this->actingAs(User::factory()->employee($acme)->create())
        ->get(tenantUrl($acme))
        ->assertOk();

    expect(Company::current()->is($acme))->toBeTrue();

    // No `flushSession()` needed: the host, not the session, is what carries the tenant now.
    $this->actingAs(User::factory()->company($globex)->create())
        ->get(tenantUrl($globex))
        ->assertOk();

    expect(Company::current()->is($globex))->toBeTrue();
});

test('a session opened on one subdomain cannot be carried to another', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    $employee = User::factory()->employee($acme)->create();

    $this->actingAs($employee)
        ->get(tenantUrl($acme))
        ->assertOk();

    // Cookies are scoped to a host, so a browser could never do this. Should the cookie be copied
    // across anyway, `EnsureUserMatchesHost` signs the employee out.
    $this->get(tenantUrl($globex))
        ->assertRedirect(tenantUrl($globex, '/login'));

    $this->assertGuest();
});

test('an employee only sees the catalogue of their own company', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    seedCatalogue($acme, 'ACME-1');
    seedCatalogue($globex, 'GLOBEX-1');

    $this->actingAs(User::factory()->employee($acme)->create())
        ->get(tenantUrl($acme))
        ->assertOk()
        ->assertSee('ACME-1')
        ->assertDontSee('GLOBEX-1');

    $this->actingAs(User::factory()->employee($globex)->create())
        ->get(tenantUrl($globex))
        ->assertOk()
        ->assertSee('GLOBEX-1')
        ->assertDontSee('ACME-1');
});

test('an administrator has no current tenant of their own', function () {
    createCompany('Acme');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk();

    expect(Company::checkCurrent())->toBeFalse();
});
