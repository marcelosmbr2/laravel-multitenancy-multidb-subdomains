<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant;
use Illuminate\Support\Facades\Route;

$centralDomain = config('multitenancy.central_domain');

// Company slugs come out of `Str::slug()`, so they are always a valid DNS label.
Route::pattern('tenant', '[a-z0-9-]+');

/*
 * The global scope, on the bare domain. `Route::domain()` is matched against the host alone, so no
 * port belongs in `central_domain`; the URL generator re-appends the port of the current request.
 *
 * Registration and password recovery also live here, but they are Fortify's routes: Fortify puts
 * everything it registers in one group under one domain, so they are registered host-agnostically
 * and kept off the subdomains by `RestrictAuthRoutesToCentralDomain`.
 */
Route::domain($centralDomain)->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    /*
     * The administration panel. No `tenant` middleware group on purpose: an administrator has no
     * tenant of their own and switches between companies as they browse, which
     * `EnsureValidTenantSession` would reject with a 401.
     */
    Route::middleware(['auth', 'role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
            Route::get('/companies/{company}', [Admin\CompanyController::class, 'show'])->name('companies.show');
        });
});

/*
 * One subdomain per company. The `{tenant}` segment is what `IdentifyTenantBySubdomain` resolved,
 * and `URL::defaults()` fills it in for `route()`, so nothing has to pass it by hand.
 */
Route::domain('{tenant}.'.$centralDomain)->group(function () {
    Route::get('/', Tenant\DashboardController::class)
        ->middleware(['auth', 'role:company,employee', 'tenant'])
        ->name('tenant.dashboard');
});

/*
 * `fortify.home` points here, and like the Fortify routes it answers on every host. It only
 * redirects, so that each role lands on its own panel.
 */
Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');
