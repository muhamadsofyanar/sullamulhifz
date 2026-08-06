@props(['name', 'size' => 20, 'label' => null])
<svg {{ $attributes->merge(['class' => 'app-icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    @switch($name)
        @case('home')
            <path d="M3 11.5 12 4l9 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5.5 10.5V20h13v-9.5M9.2 20v-5.8h5.6V20" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            @break
        @case('student')
            <circle cx="12" cy="7" r="3" class="icon-accent"/>
            <path d="M5.2 20c.5-4.5 2.8-7 6.8-7s6.3 2.5 6.8 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M4 12.5c1.5.2 2.7.8 3.6 1.8M20 12.5c-1.5.2-2.7.8-3.6 1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            @break
        @case('teacher')
            <circle cx="12" cy="7.2" r="2.8" class="icon-accent"/>
            <path d="M5.5 20c.4-4.2 2.6-6.5 6.5-6.5s6.1 2.3 6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M3 4h4M17 4h4M5 2v4M19 2v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            @break
        @case('academic')
            <rect x="4" y="3.5" width="16" height="17" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="m7.5 8 1.4 1.4 2.3-2.5M12.8 8.3H17M7.5 13l1.4 1.4 2.3-2.5M12.8 13.3H17M7.5 18l1.4 1.4 2.3-2.5M12.8 18.3H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('guidance')
            <path d="M12 3.3c4.8 0 8.5 3.5 8.5 8.2 0 4.9-3.8 8.8-8.5 8.8s-8.5-3.9-8.5-8.8c0-4.7 3.7-8.2 8.5-8.2Z" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="12" cy="12" r="2.7" class="icon-accent"/>
            <path d="M12 3.5V7M12 17v3.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            @break
        @case('report')
            <path d="M4 20V11h4v9M10 20V7h4v13M16 20V3h4v17" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="m4 8 5-3 4 1.5L20 2" stroke="var(--icon-accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('discussion')
            <path d="M4 5.5h16v11H9l-5 3v-14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="9" cy="11" r="1.1" class="icon-accent"/><circle cx="12" cy="11" r="1.1" class="icon-accent"/><circle cx="15" cy="11" r="1.1" class="icon-accent"/>
            @break
        @case('community')
            <circle cx="12" cy="7" r="2.5" class="icon-accent"/>
            <circle cx="5.5" cy="9" r="2" class="icon-accent" opacity=".85"/>
            <circle cx="18.5" cy="9" r="2" class="icon-accent" opacity=".85"/>
            <path d="M7.5 20c.3-4 1.8-6 4.5-6s4.2 2 4.5 6M2.5 19c.2-3 1.2-4.8 3-4.8 1 0 1.8.4 2.5 1.2M21.5 19c-.2-3-1.2-4.8-3-4.8-1 0-1.8.4-2.5 1.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            @break
        @case('values')
            <path d="M12 20.5S4 16 4 9.5A4.5 4.5 0 0 1 12 6a4.5 4.5 0 0 1 8 3.5c0 6.5-8 11-8 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="12" cy="10.5" r="2.2" class="icon-accent"/>
            @break
        @case('classroom')
            <path d="M4 5h16v10H4zM7 19h10M9 15v4M15 15v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="m9 10 2 2 4-4" stroke="var(--icon-accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('assignment')
            <rect x="5" y="4" width="14" height="17" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9 4.5V3h6v1.5M8.5 10h7M8.5 14h7M8.5 18h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            @break
        @case('profile')
            <circle cx="12" cy="8" r="3.3" class="icon-accent"/>
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
            <path d="M5.5 19c.8-3.3 3-5.2 6.5-5.2s5.7 1.9 6.5 5.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            @break
        @case('logout')
            <path d="M10 5H5v14h5M14 8l4 4-4 4M18 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            @break
        @case('audio')
            <path d="M9 6v12M9 8l8-2v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="6.5" cy="18" r="2.5" class="icon-accent"/>
            <circle cx="14.5" cy="15" r="2.5" class="icon-accent"/>
            @break
        @case('calendar')
            <rect x="4" y="5.5" width="16" height="14.5" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M7.5 3v5M16.5 3v5M4 10h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="9" cy="14" r="1" class="icon-accent"/><circle cx="13" cy="14" r="1" class="icon-accent"/><circle cx="17" cy="14" r="1" class="icon-accent"/>
            @break
        @default
            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
    @endswitch
</svg>
