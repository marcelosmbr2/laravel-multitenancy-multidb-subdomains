<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

/**
 * Request a reset link for the given user and return the token from the notification.
 */
function requestPasswordResetToken(User $user): string
{
    Notification::fake();

    test()->post(centralUrl('/forgot-password'), ['email' => $user->email]);

    $token = null;

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    return $token;
}

test('forgot password screen can be rendered on the bare domain', function () {
    $this->get(centralUrl('/forgot-password'))->assertOk();
});

test('a password reset link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(centralUrl('/forgot-password'), ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();

    $token = requestPasswordResetToken($user);

    $this->get(centralUrl("/reset-password/{$token}?email=".urlencode($user->email)))
        ->assertOk()
        ->assertSee($token, escape: false)
        ->assertSee($user->email);
});

test('an administrator who resets their password is sent back to the bare domain', function () {
    $admin = User::factory()->create();

    $token = requestPasswordResetToken($admin);

    $this->post(centralUrl('/reset-password'), [
        'token' => $token,
        'email' => $admin->email,
        'password' => 'my-new-password',
        'password_confirmation' => 'my-new-password',
    ])->assertRedirect(centralUrl('/login'));

    expect(Hash::check('my-new-password', $admin->fresh()->password))->toBeTrue();
});

test('a company user who resets their password is sent to the login of their subdomain', function () {
    $company = createCompany('Acme');
    $owner = User::factory()->company($company)->create();

    $token = requestPasswordResetToken($owner);

    $this->post(centralUrl('/reset-password'), [
        'token' => $token,
        'email' => $owner->email,
        'password' => 'my-new-password',
        'password_confirmation' => 'my-new-password',
    ])->assertRedirect(tenantUrl($company, '/login'));

    expect(Hash::check('my-new-password', $owner->fresh()->password))->toBeTrue();
});

test('passwords cannot be reset with an invalid token', function () {
    $user = User::factory()->create();

    requestPasswordResetToken($user);

    $this->post(centralUrl('/reset-password'), [
        'token' => 'an-invalid-token',
        'email' => $user->email,
        'password' => 'my-new-password',
        'password_confirmation' => 'my-new-password',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('my-new-password', $user->fresh()->password))->toBeFalse();
});
