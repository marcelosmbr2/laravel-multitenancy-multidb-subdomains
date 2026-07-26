<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more roles, e.g. `role:admin` or `role:company,employee`.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role;

        $allowed = array_map(
            fn (string $name): UserRole => UserRole::from($name),
            $roles
        );

        abort_unless($role !== null && in_array($role, $allowed, strict: true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
