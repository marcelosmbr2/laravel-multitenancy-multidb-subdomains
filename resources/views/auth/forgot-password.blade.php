<x-layouts.auth
    title="Forgot password"
    heading="Forgot password"
    description="Enter your email address and we'll send you a link to reset your password."
>
    <x-session-status class="mt-2" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Email</legend>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="input w-full"
                placeholder="you@example.com"
                autocomplete="username"
                required
                autofocus
            >

            <x-input-error for="email" />
        </fieldset>

        <button type="submit" class="btn mt-4 w-full">Email password reset link</button>
    </form>

    <p class="text-base-content/60 mt-2 text-center text-sm">
        <a href="{{ route('login') }}" class="link link-hover">Back to log in</a>
    </p>
</x-layouts.auth>
