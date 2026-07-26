<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
});

/**
 * @return array<string, string>
 */
function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'company_name' => 'Initech',
        'plan' => 'basic',
        'name' => 'Peter Gibbons',
        'email' => 'peter@initech.test',
        'password' => 'password-of-the-test',
        'password_confirmation' => 'password-of-the-test',
    ], $overrides);
}

test('registration screen can be rendered on the bare domain', function () {
    $this->get(centralUrl('/register'))
        ->assertOk()
        ->assertSee('Register your company')
        ->assertSee('Basic')
        ->assertSee('Premium');
});

test('registering a company creates the tenant, its database and its owner', function () {
    $this->post(centralUrl('/register'), registrationPayload())
        ->assertRedirect();

    $company = Company::firstWhere('slug', 'initech');

    expect($company)->not->toBeNull()
        ->and($company->database)->toBe('initech')
        ->and($company->plan->slug)->toBe('basic')
        ->and(File::exists($company->getDatabaseName()))->toBeTrue();

    // The tenant migrations ran into the brand new file.
    $company->execute(function (): void {
        expect(Category::count())->toBe(0)
            ->and(Product::count())->toBe(0);
    });

    $owner = User::firstWhere('email', 'peter@initech.test');

    expect($owner->role)->toBe(UserRole::Company)
        ->and($owner->company_id)->toBe($company->id);
});

test('registration hands the owner over to the login of their new subdomain', function () {
    // Signing up happens on the bare domain, and the session Fortify opens there is scoped to that
    // host. It is discarded, and the owner signs in again where their company actually lives.
    $this->post(centralUrl('/register'), registrationPayload())
        ->assertRedirect(tenantUrl(Company::firstWhere('slug', 'initech'), '/login?registered=1'));

    $this->assertGuest();
});

test('a registered company owner can sign in and reach their dashboard', function () {
    $this->post(centralUrl('/register'), registrationPayload());

    $company = Company::firstWhere('slug', 'initech');

    $this->post(tenantUrl($company, '/login'), [
        'email' => 'peter@initech.test',
        'password' => 'password-of-the-test',
    ]);

    $this->assertAuthenticatedAs(User::firstWhere('email', 'peter@initech.test'));

    $this->get(tenantUrl($company, '/dashboard'))->assertRedirect(tenantUrl($company));

    $this->get(tenantUrl($company))
        ->assertOk()
        ->assertSee('Initech');
});

test('registration is not reachable from a company subdomain', function () {
    $company = createCompany('Acme');

    $this->get(tenantUrl($company, '/register'))->assertNotFound();
    $this->post(tenantUrl($company, '/register'), registrationPayload())->assertNotFound();

    expect(Company::count())->toBe(1);
});

test('two companies with the same name get distinct databases', function () {
    $this->post(centralUrl('/register'), registrationPayload());

    $this->post(centralUrl('/register'), registrationPayload([
        'email' => 'samir@initech.test',
    ]));

    $databases = Company::orderBy('id')->pluck('database');

    expect($databases->all())->toBe(['initech', 'initech-2'])
        ->and($databases->unique())->toHaveCount(2);
});

test('registration requires a company name', function () {
    $this->post(centralUrl('/register'), registrationPayload(['company_name' => '']))
        ->assertSessionHasErrors('company_name');

    expect(Company::count())->toBe(0);
    $this->assertGuest();
});

test('registration requires a known plan', function () {
    $this->post(centralUrl('/register'), registrationPayload(['plan' => 'enterprise']))
        ->assertSessionHasErrors('plan');

    expect(Company::count())->toBe(0);
    $this->assertGuest();
});

test('registration requires a matching password confirmation', function () {
    $this->post(centralUrl('/register'), registrationPayload([
        'password_confirmation' => 'a-different-password',
    ]))->assertSessionHasErrors('password');

    expect(Company::count())->toBe(0);
    $this->assertGuest();
});

test('registration requires a unique email address', function () {
    $existing = User::factory()->create();

    $this->post(centralUrl('/register'), registrationPayload(['email' => $existing->email]))
        ->assertSessionHasErrors('email');

    expect(Company::count())->toBe(0);
});
