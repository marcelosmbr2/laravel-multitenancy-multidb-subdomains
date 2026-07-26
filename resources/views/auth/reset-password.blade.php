<x-layouts.auth
    title="Reset password"
    heading="Reset password"
    description="Choose a new password for your account."
>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Email</legend>

            {{-- The email arrives as a query string parameter on the reset link. --}}
            <input
                type="email"
                name="email"
                value="{{ old('email', $request->input('email')) }}"
                class="input w-full"
                placeholder="you@example.com"
                autocomplete="username"
                required
            >

            <x-input-error for="email" />
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">New password</legend>

            <input
                type="password"
                name="password"
                class="input w-full"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                autofocus
            >

            <x-input-error for="password" />
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Confirm password</legend>

            <input
                type="password"
                name="password_confirmation"
                class="input w-full"
                placeholder="••••••••"
                autocomplete="new-password"
                required
            >

            <x-input-error for="password_confirmation" />
        </fieldset>

        <button type="submit" class="btn mt-4 w-full">Reset password</button>
    </form>
</x-layouts.auth>
