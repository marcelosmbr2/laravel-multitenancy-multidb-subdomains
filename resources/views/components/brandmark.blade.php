@props([
    'company',
    'size' => 'md',
])

@php
    // Plain scale utilities only. Tailwind's extractor did not pick an arbitrary value such as
    // `text-[0.625rem]` out of this match expression, so the class never reached the stylesheet.
    $dimensions = match ($size) {
        'sm' => 'size-7 text-xs',
        'lg' => 'size-11 text-base',
        default => 'size-9 text-sm',
    };
@endphp

{{--
    The mark of a company: its initials over a hue derived from its slug. `aria-hidden` because the
    company name always sits right next to it, so a screen reader would only read it twice.
--}}
<span
    {{ $attributes->class(['inline-flex shrink-0 items-center justify-center rounded-md font-bold tracking-wide text-white', $dimensions]) }}
    style="background-color: hsl({{ $company->brandmarkHue() }} 60% 42%)"
    aria-hidden="true"
>{{ $company->initials() }}</span>
