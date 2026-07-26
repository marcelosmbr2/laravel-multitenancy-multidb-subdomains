<?php

use App\Models\User;

test('the shared dashboard route sends an administrator to the admin panel', function () {
    $this->actingAs(User::factory()->create())
        ->get(centralUrl('/dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('the shared dashboard route sends a company owner to the panel of their subdomain', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->company($company)->create())
        ->get(tenantUrl($company, '/dashboard'))
        ->assertRedirect(tenantUrl($company));
});

test('the shared dashboard route sends an employee to the panel of their subdomain', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->employee($company)->create())
        ->get(tenantUrl($company, '/dashboard'))
        ->assertRedirect(tenantUrl($company));
});

test('logging in on a subdomain lands on the dashboard of that company', function () {
    $company = createCompany('Acme');
    $owner = User::factory()->company($company)->create();

    $this->post(tenantUrl($company, '/login'), [
        'email' => $owner->email,
        'password' => 'password',
    ])->assertRedirect(config('fortify.home'));

    $this->get(tenantUrl($company, '/dashboard'))
        ->assertRedirect(tenantUrl($company));
});

test('the company and the employee panel are the same route with a view of their own', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->company($company)->create())
        ->get(tenantUrl($company))
        ->assertOk()
        ->assertSee('Employees');

    $this->actingAs(User::factory()->employee($company)->create())
        ->get(tenantUrl($company))
        ->assertOk()
        ->assertDontSee('Employees');
});

test('the administration panel does not exist on a company subdomain', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->create())
        ->get(tenantUrl($company, '/admin'))
        ->assertNotFound();
});

test('guests are redirected to the login screen of the host they asked for', function () {
    $company = createCompany('Acme');

    $this->get(route('admin.dashboard'))->assertRedirect(centralUrl('/login'));
    $this->get(tenantUrl($company))->assertRedirect(tenantUrl($company, '/login'));
});
