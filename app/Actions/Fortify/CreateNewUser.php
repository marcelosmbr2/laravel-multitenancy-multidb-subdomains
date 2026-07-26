<?php

namespace App\Actions\Fortify;

use App\Actions\Tenancy\CreateTenantDatabase;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Throwable;

/**
 * Registration is for companies only: it creates the tenant, provisions its database and returns
 * the company owner, whom Fortify then authenticates.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private CreateTenantDatabase $createTenantDatabase) {}

    /**
     * Validate and create a newly registered company with its owner.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'plan' => ['required', 'string', Rule::exists(Plan::class, 'slug')],
            'password' => $this->passwordRules(),
        ])->validate();

        $plan = Plan::where('slug', $input['plan'])->firstOrFail();
        $slug = $this->uniqueSlug($input['company_name']);

        // The landlord rows are committed first, so the tenant database is provisioned against a
        // company that really exists. If provisioning fails, the rows are rolled back by hand.
        [$company, $user] = DB::connection('landlord')->transaction(function () use ($input, $plan, $slug): array {
            $company = Company::create([
                'plan_id' => $plan->id,
                'name' => $input['company_name'],
                'slug' => $slug,
                'database' => $slug,
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => UserRole::Company,
            ]);

            return [$company, $user];
        });

        try {
            $this->createTenantDatabase->execute($company);
        } catch (Throwable $e) {
            $this->createTenantDatabase->delete($company);
            $company->delete();

            throw $e;
        }

        return $user;
    }

    /**
     * Subdomains that must not become a company, because they name something else.
     *
     * @var list<string>
     */
    private const RESERVED_SLUGS = ['admin', 'api', 'app', 'mail', 'static', 'www'];

    /**
     * Build a slug that is free in both the `slug` and `database` columns.
     *
     * The slug doubles as the company subdomain. `Str::slug()` already narrows it to a valid DNS
     * label, and the reserved list keeps it from shadowing a host of our own.
     */
    private function uniqueSlug(string $companyName): string
    {
        $base = Str::slug($companyName) ?: 'company';
        $slug = $base;
        $suffix = 1;

        while ($this->slugIsTaken($slug)) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }

    private function slugIsTaken(string $slug): bool
    {
        return in_array($slug, self::RESERVED_SLUGS, true)
            || Company::where('slug', $slug)->orWhere('database', $slug)->exists();
    }
}
