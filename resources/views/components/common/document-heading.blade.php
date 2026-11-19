@props(['label', 'number', 'subtitle' => null])
<div class="min-w-0 border-s-4 border-brand-500 ps-4 dark:border-brand-400">
    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <h2 class="mt-1 break-words text-xl font-semibold tracking-tight text-gray-900 dark:text-white/90 sm:text-2xl">{{ $number }}</h2>
    @if (filled($subtitle))<p class="mt-1.5 break-words text-theme-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>@endif
    {{ $slot }}
</div>
