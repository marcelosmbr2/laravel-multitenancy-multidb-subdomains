@props([
    'title' => null,
])

@php
    // Set on a company subdomain, null in the administration panel on the bare domain.
    $tenant = App\Models\Company::current();
    $brand = $tenant?->name ?? config('app.name');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' - '.$brand : $brand }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-base-200 antialiased">
        <div class="navbar bg-base-100 shadow-sm">
            <div class="navbar-start">
                <a href="{{ route(auth()->user()->role->dashboardRoute()) }}" class="btn btn-ghost gap-2 text-lg">
                    @if ($tenant)
                        <x-brandmark :company="$tenant" />
                    @endif

                    {{ $brand }}
                </a>
            </div>

            <div class="navbar-end gap-3">
                @auth
                    <div class="hidden text-right text-sm sm:block">
                        <div class="font-medium">{{ auth()->user()->name }}</div>

                        {{-- No company name here: the brandmark on the other end of the navbar
                             already says which one this is. --}}
                        <div class="text-base-content/60">{{ auth()->user()->role->label() }}</div>
                    </div>
                @endauth

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn btn-ghost">Log out</button>
                </form>
            </div>
        </div>

        <main class="mx-auto max-w-6xl p-6">
            {{ $slot }}
        </main>
    </body>
</html>
