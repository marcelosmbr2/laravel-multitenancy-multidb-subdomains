<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserMatchesHost;
use App\Http\Middleware\IdentifyTenantBySubdomain;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Exceptions\NoCurrentTenant;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Determines the current tenant from the subdomain. Needs no session, so it goes first.
        $middleware->prependToGroup('web', IdentifyTenantBySubdomain::class);

        // Signs out a user authenticated on a host that is not theirs. Must run after StartSession,
        // which appending to the `web` group guarantees.
        $middleware->appendToGroup('web', EnsureUserMatchesHost::class);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // For routes that cannot work without a tenant, which is every route of a company
        // subdomain. It must NOT be applied to the admin panel: an administrator has no tenant of
        // their own and browses companies through `Company::execute()`.
        //
        // `EnsureValidTenantSession` is deliberately absent. It pins a session to the first tenant
        // it sees and answers a bare 401 on a mismatch, which was worth having while the tenant
        // came from the authenticated user. Now that it comes from the host,
        // `EnsureUserMatchesHost` covers the same replayed-cookie case, and signs the user out
        // instead of stranding them on a 401.
        $middleware->group('tenant', [
            NeedsTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // `IdentifyTenantBySubdomain` already answers 404 for a host that names no company, so
        // `NeedsTenant` should never fire. If it ever does, the request asked a company subdomain
        // for something on a host that has no company, which is the same 404.
        $exceptions->render(function (NoCurrentTenant $e, Request $request) {
            abort(404);
        });
    })->create();
