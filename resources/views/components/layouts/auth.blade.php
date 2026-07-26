@props([
    'title' => null,
    'heading' => null,
    'description' => null,
])

@php
    // Set on a company subdomain, null on the bare domain.
    $tenant = App\Models\Company::current();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' - '.($tenant?->name ?? config('app.name')) : ($tenant?->name ?? config('app.name')) }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-base-200 antialiased">
        <div class="grid min-h-screen place-items-center p-6">
            <div class="w-full max-w-sm">
                <div class="mb-6 flex items-center justify-center gap-2 text-xl font-semibold">
                    @if ($tenant)
                        {{-- Not a link: on a company subdomain `/` is the dashboard, which would
                             only send a signed out visitor straight back to this page. --}}
                        <x-brandmark :company="$tenant" size="lg" />

                        {{ $tenant->name }}
                    @else
                        <a href="{{ App\Models\Company::centralUrl() }}">{{ config('app.name') }}</a>
                    @endif
                </div>

                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        @if ($heading)
                            <h1 class="card-title">{{ $heading }}</h1>
                        @endif

                        @if ($description)
                            <p class="text-base-content/60 text-sm">{{ $description }}</p>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
