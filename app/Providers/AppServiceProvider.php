<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Registering the landlord directory keeps plain `php artisan migrate` working: Laravel does
     * not recurse into subdirectories of `database/migrations`. Tenant migrations are deliberately
     * *not* registered here — they are run against the `tenant` connection with an explicit
     * `--path`, which makes the migrator ignore the registered paths.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/landlord'));
    }
}
