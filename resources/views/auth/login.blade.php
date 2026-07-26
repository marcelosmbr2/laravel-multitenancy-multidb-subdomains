@php
    // On a company subdomain this is that company's own login page, and the layout brands it with
    // the company. On the bare domain it is the administration login, and the only place a new
    // company can be signed up from.
    $tenant = App\Models\Company::current();
@endphp

<x-layouts.auth
    title="Log in"
    heading="Log in"
    description="Enter your email and password below to continue."
>
    @if ($tenant && request()->boolean('registered'))
        <div role="alert" class="alert alert-success alert-soft mt-2">
            {{ $tenant->name }} is ready. Sign in to continue.
        </div>
    @endif

    <x-session-status class="mt-2" />

    <form method="POST" action="{{ route('login') }}">
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

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Password</legend>

            <input
                type="password"
                name="password"
                class="input w-full"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >

            <x-input-error for="password" />
        </fieldset>

        <div class="mt-1 flex items-center justify-between">
            <label class="label">
                <input type="checkbox" name="remember" class="checkbox" @checked(old('remember'))>
                Remember me
            </label>

            @if (Route::has('password.request'))
                {{-- Password recovery is global, so the link leaves the subdomain. --}}
                <a href="{{ App\Models\Company::centralUrl('/forgot-password') }}" class="link link-hover text-sm">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn mt-4 w-full">Log in</button>
    </form>

    @if (! $tenant && Route::has('register'))
        <p class="text-base-content/60 mt-2 text-center text-sm">
            Want to register a company?
            <a href="{{ route('register') }}" class="link link-hover">Sign up</a>
        </p>
    @endif
</x-layouts.auth>
