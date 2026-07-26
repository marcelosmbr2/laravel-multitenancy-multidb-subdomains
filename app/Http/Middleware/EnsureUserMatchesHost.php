<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out any user who is authenticated on a host that is not theirs.
 *
 * `Fortify::authenticateUsing()` already refuses the credentials of a user who does not belong to
 * the host they are logging in on, but that only covers the password form and the two factor
 * challenge. This closes the rest: a session cookie copied from one subdomain to another, a
 * passkey whose relying party covers every subdomain, and a session left behind by a user that was
 * moved to another company.
 *
 * Appended to the `web` group, so it runs after `StartSession` and the user is resolvable.
 */
class EnsureUserMatchesHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->belongsToTenant(Company::current())) {
            return $next($request);
        }

        Auth::guard(config('fortify.guard'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Please sign in again on this address.');
    }
}
