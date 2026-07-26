<?php

use App\Models\Company;
use App\Models\User;

test('a host that names no company does not exist', function () {
    $unknown = 'http://nobody.'.config('multitenancy.central_domain');

    $this->get($unknown.'/')->assertNotFound();
    $this->get($unknown.'/login')->assertNotFound();
});

test('a deeper host than one subdomain does not exist', function () {
    $company = createCompany('Acme');

    $this->get('http://a.'.$company->domain().'/login')->assertNotFound();
});

test('password recovery only answers on the bare domain', function () {
    $company = createCompany('Acme');

    $this->get(tenantUrl($company, '/forgot-password'))->assertNotFound();
    $this->post(tenantUrl($company, '/forgot-password'), ['email' => 'someone@acme.test'])->assertNotFound();
    $this->get(tenantUrl($company, '/reset-password/a-token'))->assertNotFound();

    $this->get(centralUrl('/forgot-password'))->assertOk();
});

test('a user of one company cannot sign in on the subdomain of another', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    $owner = User::factory()->company($acme)->create();

    $this->post(tenantUrl($globex, '/login'), [
        'email' => $owner->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an administrator cannot sign in on a company subdomain', function () {
    $company = createCompany('Acme');
    $admin = User::factory()->create();

    $this->post(tenantUrl($company, '/login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a company user cannot sign in on the bare domain', function () {
    $company = createCompany('Acme');
    $owner = User::factory()->company($company)->create();

    $this->post(centralUrl('/login'), [
        'email' => $owner->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the company of the subdomain is current before anyone has signed in', function () {
    $company = createCompany('Acme');

    $this->get(tenantUrl($company, '/login'))->assertOk();

    expect(Company::current()->is($company))->toBeTrue();
});

test('a company cannot take a reserved subdomain', function () {
    $this->seed(Database\Seeders\PlanSeeder::class);

    $this->post(centralUrl('/register'), [
        'company_name' => 'WWW',
        'plan' => 'basic',
        'name' => 'Peter Gibbons',
        'email' => 'peter@www.test',
        'password' => 'password-of-the-test',
        'password_confirmation' => 'password-of-the-test',
    ]);

    expect(Company::firstWhere('slug', 'www'))->toBeNull()
        ->and(Company::sole()->slug)->toBe('www-2');
});
