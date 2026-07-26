<?php

namespace App\Actions\Tenancy;

use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Provisions the SQLite database of a company and runs the tenant migrations into it.
 *
 * spatie/laravel-multitenancy deliberately does not create databases ("Because there are so many
 * ways to go about it, the package does not handle creating databases"), so this is ours.
 */
class CreateTenantDatabase
{
    public function execute(Company $company, bool $fresh = false): void
    {
        $path = $company->getDatabaseName();

        File::ensureDirectoryExists(dirname($path));

        if ($fresh) {
            File::delete($path);
        }

        // The file has to exist before anything connects to it: SQLiteConnector throws
        // SQLiteDatabaseDoesNotExistException rather than creating it.
        if (! File::exists($path)) {
            File::put($path, '');
        }

        $company->execute(fn () => Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]));
    }

    public function delete(Company $company): void
    {
        File::delete($company->getDatabaseName());
    }
}
