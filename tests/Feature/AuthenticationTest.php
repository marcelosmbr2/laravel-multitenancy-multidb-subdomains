<?php

use App\Models\User;

test('the administration login screen can be rendered on the bare domain', function () {
    $this->get(centralUrl('/login'))->assertOk();
});

test('a company login screen is branded with the company of the subdomain', function () {
    $company = createCompany('Acme');

    $this->get(tenantUrl($company, '/login'))
        ->assertOk()
        ->assertSee('Acme');
});

test('an administrator can authenticate on the bare domain', function () {
    $admin = User::factory()->create();

    $this->post(centralUrl('/login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(config('fortify.home'));

    $this->assertAuthenticatedAs($admin);
});

test('a company owner can authenticate on their own subdomain', function () {
    $company = createCompany('Acme');
    $owner = User::factory()->company($company)->create();

    $this->post(tenantUrl($company, '/login'), [
        'email' => $owner->email,
        'password' => 'password',
    ])->assertRedirect(config('fortify.home'));

    $this->assertAuthenticatedAs($owner);
});

test('users cannot authenticate with an invalid password', function () {
    $user = User::factory()->create();

    $this->post(centralUrl('/login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logging out lands back on the login screen of the host it happened on', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->company($company)->create())
        ->post(tenantUrl($company, '/logout'))
        ->assertRedirect(tenantUrl($company, '/login'));

    $this->assertGuest();
});

test('the dashboard is not accessible to guests', function () {
    $this->get(centralUrl('/dashboard'))->assertRedirect(centralUrl('/login'));
});

test('the dashboard hands authenticated users over to the dashboard of their role', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(centralUrl('/dashboard'))
        ->assertRedirect(route('admin.dashboard'));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee($admin->name);
});
