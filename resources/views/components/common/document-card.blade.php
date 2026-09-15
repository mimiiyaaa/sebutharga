@props(['title', 'desc' => ''])
<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900']) }}>
    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/30">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        @if ($desc)<p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p>@endif
    </div>
    <div class="space-y-5 p-5 sm:p-6">{{ $slot }}</div>
</section>
