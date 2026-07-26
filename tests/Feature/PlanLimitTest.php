<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
});

test('the basic plan caps employees at five', function () {
    $company = createCompany('Acme', 'basic');

    expect($company->plan->max_employees)->toBe(5)
        ->and($company->canAddEmployee())->toBeTrue()
        ->and($company->remainingEmployees())->toBe(5);

    User::factory()->count(5)->employee($company)->create();

    expect($company->employeesCount())->toBe(5)
        ->and($company->remainingEmployees())->toBe(0)
        ->and($company->canAddEmployee())->toBeFalse();
});

test('the premium plan allows far more employees', function () {
    $company = createCompany('Globex', 'premium');

    User::factory()->count(5)->employee($company)->create();

    expect($company->plan->max_employees)->toBe(50)
        ->and($company->canAddEmployee())->toBeTrue()
        ->and($company->remainingEmployees())->toBe(45);
});

test('the company owner does not count against the employee limit', function () {
    $company = createCompany('Acme', 'basic');

    User::factory()->company($company)->create();
    User::factory()->count(2)->employee($company)->create();

    expect($company->employeesCount())->toBe(2)
        ->and($company->users()->count())->toBe(3);
});

test('product limits count rows in the tenant database', function () {
    $company = createCompany('Acme', 'basic');

    expect($company->productsCount())->toBe(0)
        ->and($company->remainingProducts())->toBe(20);

    $company->execute(function (): void {
        $category = Category::create(['name' => 'Tools']);

        Product::factory()->count(3)->create(['category_id' => $category->id]);
    });

    expect($company->productsCount())->toBe(3)
        ->and($company->remainingProducts())->toBe(17)
        ->and($company->canAddProduct())->toBeTrue();
});

test('the company dashboard shows plan usage', function () {
    $company = createCompany('Acme', 'basic');

    User::factory()->count(2)->employee($company)->create();

    $this->actingAs(User::factory()->company($company)->create())
        ->get(tenantUrl($company))
        ->assertOk()
        ->assertSee('Basic')
        ->assertSee('3 remaining');
});
