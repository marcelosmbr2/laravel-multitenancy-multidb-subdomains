<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * Every login of the application lives in this single landlord table, whatever the role.
 *
 * Administrators have no tenant to look their credentials up in, and a company subdomain has to be
 * able to reject an e-mail that belongs to another company without first connecting to that other
 * company's database. A single landlord table answers both.
 */
#[Fillable(['company_id', 'name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use UsesLandlordConnection;

    /**
     * The company this user belongs to, and therefore the tenant made current for them.
     *
     * Null for administrators, who are global.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Whether this user is allowed to be signed in while the given tenant is the current one.
     *
     * On a company subdomain that means being a member of exactly that company. On the bare domain,
     * where there is no tenant, it means being an administrator.
     */
    public function belongsToTenant(?Company $tenant): bool
    {
        return $tenant
            ? $this->company_id === $tenant->getKey()
            : $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCompany(): bool
    {
        return $this->role === UserRole::Company;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
