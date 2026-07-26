<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the company whose subdomain was requested the current tenant.
 *
 * This is the mechanism that determines the current tenant in this application. A
 * `Spatie\Multitenancy\TenantFinder\TenantFinder` is not used for it, even though the tenant is now
 * identified by the host: the package only invokes the finder when the application is not
 * `runningInConsole()`, and it does so once, during the service provider boot phase. Under PHPUnit
 * `PHP_SAPI` is `cli`, which would leave every feature test without a tenant. Prepended to the
 * `web` group, this runs on every request instead, however that request was made.
 *
 * It needs no session, so it is deliberately the first thing the group does.
 */
class IdentifyTenantBySubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $centralDomain = config('multitenancy.central_domain');
        $host = $request->getHost();

        if ($host === $centralDomain) {
            Company::forgetCurrent();

            return $next($request);
        }

        abort_unless(str_ends_with($host, '.'.$centralDomain), Response::HTTP_NOT_FOUND);

        $slug = Str::before($host, '.'.$centralDomain);
        $company = Company::firstWhere('slug', $slug);

        abort_if($company === null, Response::HTTP_NOT_FOUND);

        $company->makeCurrent();

        // Lets `route('tenant.dashboard')` resolve without the caller having to know the subdomain.
        URL::defaults(['tenant' => $slug]);

        return $next($request);
    }
}
