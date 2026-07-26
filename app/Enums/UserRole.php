<?php

namespace App\Enums;

enum UserRole: string
{
    /**
     * A global user. Belongs to no company and can inspect the data of every one of them.
     */
    case Admin = 'admin';

    /**
     * The company itself. Owns a tenant and manages it.
     */
    case Company = 'company';

    /**
     * An employee of a company.
     */
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Company => 'Company',
            self::Employee => 'Employee',
        };
    }

    /**
     * The named route of the dashboard this role lands on after authenticating.
     *
     * Company and employee share one, on the company subdomain: the host already identifies the
     * tenant, and the action behind it picks the view for the role.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Company, self::Employee => 'tenant.dashboard',
        };
    }

    /**
     * Whether this role is scoped to a single company, and therefore needs a current tenant.
     */
    public function isTenantScoped(): bool
    {
        return $this !== self::Admin;
    }
}
