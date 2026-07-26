@php
    $plans = App\Models\Plan::orderBy('max_products')->get();
@endphp

<x-layouts.auth
    title="Register"
    heading="Register your company"
    description="Sign-up is for companies. Your company gets a database of its own."
>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Company name</legend>

            <input
                type="text"
                name="company_name"
                value="{{ old('company_name') }}"
                class="input w-full"
                placeholder="Acme"
                required
                autofocus
            >

            <x-input-error for="company_name" />
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Plan</legend>

            <select name="plan" class="select w-full" required>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->slug }}" @selected(old('plan', 'basic') === $plan->slug)>
                        {{ $plan->name }} — {{ $plan->max_employees }} employees, {{ $plan->max_products }} products
                    </option>
                @endforeach
            </select>

            <x-input-error for="plan" />
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Your name</legend>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="input w-full"
                placeholder="Your name"
                autocomplete="name"
                required
            >

            <x-input-error for="name" />
        </fieldset>

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
                autocomplete="new-password"
                required
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

        <button type="submit" class="btn mt-4 w-full">Register</button>
    </form>

    <p class="text-base-content/60 mt-2 text-center text-sm">
        Already have an account?
        <a href="{{ route('login') }}" class="link link-hover">Log in</a>
    </p>
</x-layouts.auth>
