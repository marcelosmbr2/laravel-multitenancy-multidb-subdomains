<?php

namespace App\Http\Responses;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends someone who just reset their password to the login page that will accept them.
 *
 * Password recovery only answers on the bare domain, since a company user has to be able to
 * recover an account whatever subdomain they remember. Fortify would then leave them on the login
 * of that bare domain, which only administrators can sign in to.
 */
class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(private string $status) {}

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($this->status)], 200);
        }

        // `redirect()` rather than a bare `RedirectResponse`: only the redirector binds the session
        // the flashed status needs.
        return redirect()
            ->to($this->loginUrlFor($request))
            ->with('status', trans($this->status));
    }

    /**
     * The reset has already succeeded by the time this runs, so the address does belong to a user.
     */
    private function loginUrlFor($request): string
    {
        $user = User::firstWhere('email', $request->input(Fortify::email()));

        return $user?->company?->url('/login') ?? Company::centralUrl('/login');
    }
}
