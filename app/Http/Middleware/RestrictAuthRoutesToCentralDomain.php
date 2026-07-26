<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hides the global authentication routes from the company subdomains.
 *
 * Fortify registers all of its routes in a single group, under a single `fortify.domain`, so it
 * cannot answer on the bare domain and on the subdomains at the same time. The domain is therefore
 * left `null` - which matches every host - and this middleware, added to `fortify.middleware`,
 * takes the routes that must stay global back off the subdomains.
 *
 * `login`, `login.store` and `logout` are absent from the list on purpose: they are what gives an
 * administrator a login on the bare domain and every company a login of its own.
 */
class RestrictAuthRoutesToCentralDomain
{
    /**
     * Signing up creates a company, and password recovery has to work for someone who cannot reach
     * their own subdomain, so both only ever answer on the bare domain.
     *
     * @var list<string>
     */
    private const CENTRAL_ONLY_ROUTES = [
        'register',
        'register.store',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $isCentralOnly = in_array($request->route()?->getName(), self::CENTRAL_ONLY_ROUTES, true);

        abort_if($isCentralOnly && Company::checkCurrent(), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}
