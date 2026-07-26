<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;

function seedProduct(Company $company, string $sku): void
{
    $company->execute(function () use ($sku): void {
        $category = Category::create(['name' => 'Catalogue']);

        Product::create([
            'category_id' => $category->id,
            'sku' => $sku,
            'name' => "Product {$sku}",
            'price' => 25.00,
            'quantity' => 7,
        ]);
    });
}

test('the admin panel lists every company with its tenant counts', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    seedProduct($acme, 'ACME-1');
    User::factory()->count(2)->employee($acme)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Acme')
        ->assertSee('Globex')
        ->assertSee('acme.sqlite')
        ->assertSee($globex->database.'.sqlite')
        ->assertSee($acme->domain())
        ->assertSee($globex->domain());
});

test('an administrator can read the data of any company', function () {
    $acme = createCompany('Acme');

    seedProduct($acme, 'ACME-1');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.companies.show', $acme))
        ->assertOk()
        ->assertSee('ACME-1');
});

test('an administrator can visit two companies in the same session', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    seedProduct($acme, 'ACME-1');
    seedProduct($globex, 'GLOBEX-1');

    // The admin routes deliberately skip the `tenant` middleware group: an administrator has no
    // tenant of their own and makes one company current after another through `Company::execute()`.
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.companies.show', $acme))
        ->assertOk()
        ->assertSee('ACME-1')
        ->assertDontSee('GLOBEX-1');

    $this->get(route('admin.companies.show', $globex))
        ->assertOk()
        ->assertSee('GLOBEX-1')
        ->assertDontSee('ACME-1');
});

test('a company owner is signed out of the bare domain rather than reaching the admin panel', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    $this->actingAs(User::factory()->company($acme)->create())
        ->get(route('admin.companies.show', $globex))
        ->assertRedirect(centralUrl('/login'));

    $this->assertGuest();
});
