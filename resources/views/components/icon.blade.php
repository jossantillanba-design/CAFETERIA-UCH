@props([
    'name' => 'info',
    'class' => 'w-5 h-5',
])

@php
    $trazos = [
        'add' => '<path d="M12 5v14M5 12h14" />',
        'add_shopping_cart' => '<path d="M3 4h2l2.4 11h9.2l2.4-8H7" /><path d="M9 9h6M12 6v6" /><circle cx="10" cy="20" r="1" /><circle cx="18" cy="20" r="1" />',
        'arrow_forward' => '<path d="M5 12h14M13 6l6 6-6 6" />',
        'block' => '<circle cx="12" cy="12" r="9" /><path d="m5.6 5.6 12.8 12.8" />',
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" />',
        'check' => '<path d="m5 12 4 4L19 6" />',
        'close' => '<path d="M18 6 6 18M6 6l12 12" />',
        'delete' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6" />',
        'expand_more' => '<path d="m6 9 6 6 6-6" />',
        'info' => '<circle cx="12" cy="12" r="9" /><path d="M12 8h.01M11 12h1v5h1" />',
        'local_cafe' => '<path d="M4 8h13v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8Z" /><path d="M17 9h2a2 2 0 0 1 0 4h-2M7 3v2M11 3v2" />',
        'local_drink' => '<path d="M6 4h12l-1 16H7L6 4Z" /><path d="M7 9h10M12 9v11" />',
        'location' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="2.5" />',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16" />',
        'restaurant' => '<path d="M7 3v7a2 2 0 0 0 4 0V3M9 12v9" /><path d="M17 3c-1.5 2-2 4-2 7h4V3M17 10v11" />',
        'salad' => '<path d="M4 14h16a8 8 0 0 0-16 0Z" /><path d="M12 14V6M8 11l4 3M16 11l-4 3" />',
        'schedule' => '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l4 2" />',
        'school' => '<path d="M12 4 2 9l10 5 10-5-10-5Z" /><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5" />',
        'search' => '<circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />',
        'shopping_bag' => '<path d="M6 8h12l1 12H5L6 8Z" /><path d="M9 8V6a3 3 0 0 1 6 0v2" />',
        'star' => '<path d="m12 3 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9L12 3Z" />',
    ];
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.8"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true"
     class="{{ $class }}">
    {!! $trazos[$name] ?? $trazos['info'] !!}
</svg>
