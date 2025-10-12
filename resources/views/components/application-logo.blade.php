@props(['class' => ''])

<img src="{{ asset('images/home_icon.png') }}" alt="FNS Logo" {{ $attributes->merge(['class' => 'h-9 w-auto ' . $class]) }}>