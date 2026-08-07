@props(['name', 'size' => 20, 'label' => null])
@php($icon = match($name) {
    'academic' => 'plan',
    'assignment' => 'plan',
    'audio' => 'listen',
    'calendar' => 'schedule',
    'classroom' => 'lesson',
    'report' => 'progress',
    default => $name,
})
<svg {{ $attributes->merge(['class' => 'app-icon icon-'.$icon]) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    @switch($icon)
        @case('home')
            <path d="M4.2 9.4C4.2 15 7.3 19.2 12 22c4.7-2.8 7.8-7 7.8-12.6-2.5.2-4.8 1.4-6.2 3.5V7.5h-3.2v5.4C9 10.8 6.7 9.6 4.2 9.4Z" fill="currentColor"/>
            <path d="M12 2c2 2.1 2 4.3 0 6.4C10 6.3 10 4.1 12 2Z" fill="var(--icon-accent)"/>
            <circle cx="12" cy="10.7" r="1.7" fill="var(--icon-accent)"/>
            @break
        @case('student')
            <circle cx="12" cy="6.6" r="3.1" fill="var(--icon-accent)"/>
            <path d="M11 21V12.4c-3.2.2-5.8 1.9-7.5 4.8 3.1.2 5.6 1.4 7.5 3.8Zm2 0v-8.6c3.2.2 5.8 1.9 7.5 4.8-3.1.2-5.6 1.4-7.5 3.8Z" fill="currentColor"/>
            @break
        @case('teacher')
            <path d="M12 21a9 9 0 1 1 8.3-12.5l-3 1.2A5.8 5.8 0 1 0 12 17.8V21Z" fill="currentColor"/>
            <path d="M14.5 2.3c.1 2.8-1.1 4.6-3.8 5.4-.1-2.8 1.2-4.6 3.8-5.4Z" fill="var(--icon-accent)"/>
            <circle cx="12" cy="12" r="2.2" fill="var(--icon-accent)"/>
            @break
        @case('community')
            <circle cx="12" cy="5.5" r="2.5" fill="var(--icon-accent)"/>
            <circle cx="5.5" cy="8" r="2" fill="var(--icon-accent)"/>
            <circle cx="18.5" cy="8" r="2" fill="var(--icon-accent)"/>
            <path d="M12 22c-2.7-4.8-5.1-7-7.5-7.8 3.4-1.7 6-.8 7.5 2.1 1.5-2.9 4.1-3.8 7.5-2.1-2.4.8-4.8 3-7.5 7.8Z" fill="currentColor"/>
            @break
        @case('guidance')
            <path d="M12 21a9 9 0 1 1 8.4-12.2l-3.1 1.1A5.7 5.7 0 1 0 12 17.7V21Z" fill="currentColor"/>
            <path d="M12 2c2 2.2 2 4.4 0 6.5-2-2.1-2-4.3 0-6.5Z" fill="var(--icon-accent)"/>
            <circle cx="12" cy="11.8" r="2.2" fill="var(--icon-accent)"/>
            @break
        @case('growth')
            <path d="M11 22V11.5c-3.6.2-6.1 2-7.4 5.3 3.4-.2 5.8 1 7.4 3.7Zm2 0V11.5c3.6.2 6.1 2 7.4 5.3-3.4-.2-5.8 1-7.4 3.7Z" fill="currentColor"/>
            <path d="M12 2c2.3 2.4 2.3 4.8 0 7.1C9.7 6.8 9.7 4.4 12 2Z" fill="var(--icon-accent)"/>
            @break
        @case('preservation')
            <path d="M12 2.2 21 6v5.9c0 5.1-3.2 8.6-9 10.1-5.8-1.5-9-5-9-10.1V6l9-3.8Zm0 4L6.5 8.5v3.4c0 3.2 1.8 5.4 5.5 6.6 3.7-1.2 5.5-3.4 5.5-6.6V8.5L12 6.2Z" fill="currentColor"/>
            <circle cx="12" cy="12" r="2.2" fill="var(--icon-accent)"/>
            @break
        @case('continuity')
            <path d="M18.4 6.1A8.4 8.4 0 1 0 20.2 15h-3.6a5.1 5.1 0 1 1-.7-6.2l-2.3 2.3H21V3.8l-2.6 2.3Z" fill="currentColor"/>
            <circle cx="11.8" cy="12.2" r="2.3" fill="var(--icon-accent)"/>
            @break
        @case('focus')
            <path d="M12 22A10 10 0 1 1 21.4 8.6l-3.2 1.1A6.7 6.7 0 1 0 12 18.7V22Z" fill="currentColor"/>
            <path d="M12 2.2c2 2.1 2 4.1 0 6.1-2-2-2-4 0-6.1Z" fill="var(--icon-accent)"/>
            <circle cx="12" cy="12" r="2.5" fill="var(--icon-accent)"/>
            @break
        @case('progress')
            <path d="M3 20h4v-6H3v6Zm7 0h4V9h-4v11Zm7 0h4V4h-4v16Z" fill="currentColor"/>
            <path d="M18.6 2.3c.1 2.5-1 4.1-3.4 4.9-.1-2.5 1-4.1 3.4-4.9Z" fill="var(--icon-accent)"/>
            @break
        @case('achievement')
            <path d="M12 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16Zm0 3.2a4.8 4.8 0 1 0 0 9.6 4.8 4.8 0 0 0 0-9.6Z" fill="currentColor"/>
            <circle cx="12" cy="10" r="2.5" fill="var(--icon-accent)"/>
            <path d="m8.2 17.2-1 4.8 4.8-2.2 4.8 2.2-1-4.8a9.3 9.3 0 0 1-7.6 0Z" fill="var(--icon-accent)"/>
            @break
        @case('values')
            <path d="M12 21.5C8.3 18.8 3.2 15.3 3.2 9.7A5 5 0 0 1 12 6.4a5 5 0 0 1 8.8 3.3c0 5.6-5.1 9.1-8.8 11.8Z" fill="currentColor"/>
            <circle cx="12" cy="11" r="2.3" fill="var(--icon-accent)"/>
            @break
        @case('schedule')
            <rect x="3" y="5" width="18" height="16" rx="2.8" fill="currentColor"/>
            <path d="M7 2v5M17 2v5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M6 10h12v8H6z" fill="#fff" opacity=".97"/>
            <circle cx="9" cy="13" r="1.1" fill="var(--icon-accent)"/><circle cx="13" cy="13" r="1.1" fill="var(--icon-accent)"/><circle cx="17" cy="13" r="1.1" fill="var(--icon-accent)"/>
            <circle cx="9" cy="17" r="1.1" fill="var(--icon-accent)"/><circle cx="13" cy="17" r="1.1" fill="var(--icon-accent)"/><circle cx="17" cy="17" r="1.1" fill="var(--icon-accent)"/>
            @break
        @case('plan')
            <rect x="4" y="2" width="16" height="20" rx="3" fill="currentColor"/>
            <path d="m7 8 1.4 1.4L11 6.8M12.8 8H17M7 13l1.4 1.4L11 11.8M12.8 13H17M7 18l1.4 1.4L11 16.8M12.8 18H17" stroke="var(--icon-accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('lesson')
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/>
            <path d="m10 8 6 4-6 4V8Z" fill="var(--icon-accent)"/>
            @break
        @case('listen')
            <path d="M4 13a8 8 0 0 1 16 0v6h-4v-6a4 4 0 0 0-8 0v6H4v-6Z" fill="currentColor"/>
            <rect x="9" y="11" width="2" height="7" rx="1" fill="var(--icon-accent)"/><rect x="13" y="9" width="2" height="9" rx="1" fill="var(--icon-accent)"/>
            @break
        @case('discussion')
            <path d="M3 4h18v14H9l-5.2 3 .9-3H3V4Z" fill="currentColor"/>
            <circle cx="8" cy="11" r="1.25" fill="var(--icon-accent)"/><circle cx="12" cy="11" r="1.25" fill="var(--icon-accent)"/><circle cx="16" cy="11" r="1.25" fill="var(--icon-accent)"/>
            @break
        @case('profile')
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/>
            <circle cx="12" cy="8.5" r="2.6" fill="var(--icon-accent)"/>
            <path d="M6.5 19c.7-4 2.5-6 5.5-6s4.8 2 5.5 6" fill="currentColor"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            @break
        @case('logout')
            <path d="M10 4H4v16h6v-3H7V7h3V4Zm5 3-2.1 2.1 1.4 1.4H9v3h5.3l-1.4 1.4L15 17l5-5-5-5Z" fill="currentColor"/>
            @break
        @case('plus')
            <circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M12 7v10M7 12h10" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
            @break
        @default
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.4"/><circle cx="12" cy="12" r="2.4" fill="var(--icon-accent)"/>
    @endswitch
</svg>
