<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Concerns\ImplementsTenant;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * A company is the tenant of this application. The row itself lives in the landlord database,
 * while its categories and products live in a SQLite file of its own.
 *
 * The `slug` column doubles as the subdomain the company answers on, which is how
 * `App\Http\Middleware\IdentifyTenantBySubdomain` recognises it.
 */
#[Fillable(['plan_id', 'name', 'slug', 'database', 'document', 'phone'])]
class Company extends Model implements IsTenant
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use ImplementsTenant;
    use UsesLandlordConnection;

    /**
     * Resolve the tenant database to an absolute SQLite path.
     *
     * `ImplementsTenant` returns the raw `database` column, which is only a slug here. SQLite needs
     * a real path, and `Illuminate\Database\Connectors\SQLiteConnector` refuses to connect when the
     * file does not exist — see `App\Actions\Tenancy\CreateTenantDatabase`.
     */
    public function getDatabaseName(): string
    {
        return base_path(
            config('multitenancy.tenant_database_directory').'/'.$this->database.'.sqlite'
        );
    }

    /**
     * The host this company answers on, without a scheme or a port.
     */
    public function domain(): string
    {
        return $this->slug.'.'.config('multitenancy.central_domain');
    }

    /**
     * An absolute URL inside this company's subdomain.
     *
     * Built from `app.url` rather than from the current request, so that it also holds in a queued
     * job, in an Artisan command and in a test, where there may be no request at all.
     */
    public function url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return Str::before($base, '://').'://'.$this->slug.'.'.Str::after($base, '://').'/'.ltrim($path, '/');
    }

    /**
     * An absolute URL on the bare domain, where registration, password recovery and the
     * administration panel live.
     */
    public static function centralUrl(string $path = '/'): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * The two letters of the brandmark.
     *
     * `Str::initials()` gives one letter per word, which is too few for a one-word name and too
     * many from three words on, so both ends are trimmed back to two.
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, capitalize: true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 2)
            : Str::upper(Str::substr(Str::squish($this->name), 0, 2));
    }

    /**
     * The hue of the brandmark, stable for a given slug.
     *
     * Saturation and lightness are fixed by the `x-brandmark` component, so every company ends up
     * with the same contrast against white text, in both the light and the dark theme.
     */
    public function brandmarkHue(): int
    {
        return abs(crc32($this->slug)) % 360;
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Every login attached to this company: the company user itself and its employees.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function employees(): HasMany
    {
        return $this->users()->where('role', UserRole::Employee);
    }

    /**
     * Employees are landlord data, so this counts without switching tenants.
     */
    public function employeesCount(): int
    {
        return $this->employees()->count();
    }

    /**
     * Products live in the tenant database, so the count has to run inside the tenant context.
     */
    public function productsCount(): int
    {
        return $this->execute(fn (): int => Product::count());
    }

    public function categoriesCount(): int
    {
        return $this->execute(fn (): int => Category::count());
    }

    public function remainingEmployees(): int
    {
        return max(0, $this->plan->max_employees - $this->employeesCount());
    }

    public function remainingProducts(): int
    {
        return max(0, $this->plan->max_products - $this->productsCount());
    }

    public function canAddEmployee(): bool
    {
        return $this->remainingEmployees() > 0;
    }

    public function canAddProduct(): bool
    {
        return $this->remainingProducts() > 0;
    }
}
