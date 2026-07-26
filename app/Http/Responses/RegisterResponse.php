<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends a freshly registered company owner to the login page of their own subdomain.
 *
 * Registration happens on the bare domain, and Fortify has just signed the owner in there. That
 * session is of no use: the cookie is scoped to the host it was set on, and the company panel only
 * exists on the subdomain. So it is discarded and the owner starts again where they belong.
 */
class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        $company = $request->user()->company;

        Auth::guard(config('fortify.guard'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return new JsonResponse(['redirect' => $company->url('/login')], 201);
        }

        return redirect()->to($company->url('/login?registered=1'));
    }
}
