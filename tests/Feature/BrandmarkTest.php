<?php

use App\Models\User;

test('a company login screen leads with the brandmark and the company name', function () {
    $company = createCompany('Acme');

    $this->get(tenantUrl($company, '/login'))
        ->assertOk()
        ->assertSee('Acme')
        ->assertSee($company->initials())
        ->assertSee('hsl('.$company->brandmarkHue(), escape: false)
        ->assertSee('Log in');
});

test('the login screen of the bare domain carries no brandmark', function () {
    $this->get(centralUrl('/login'))
        ->assertOk()
        ->assertSee(config('app.name'))
        ->assertDontSee('hsl(', escape: false);
});

test('the company panel is branded with the company and headed Dashboard', function () {
    $company = createCompany('Acme');

    $this->actingAs(User::factory()->company($company)->create())
        ->get(tenantUrl($company))
        ->assertOk()
        ->assertSee('Acme')
        ->assertSee($company->initials())
        ->assertSee('>Dashboard<', escape: false);
});

test('the employee panel is headed Dashboard rather than a greeting', function () {
    $company = createCompany('Acme');
    $employee = User::factory()->employee($company)->create();

    $this->actingAs($employee)
        ->get(tenantUrl($company))
        ->assertOk()
        ->assertSee($company->initials())
        ->assertSee('>Dashboard<', escape: false)
        ->assertDontSee('Hello,');
});

test('the administration panel carries no brandmark of its own but marks every company', function () {
    $acme = createCompany('Acme');
    $globex = createCompany('Globex', 'premium');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(config('app.name'))
        ->assertSee('hsl('.$acme->brandmarkHue(), escape: false)
        ->assertSee('hsl('.$globex->brandmarkHue(), escape: false);
});
