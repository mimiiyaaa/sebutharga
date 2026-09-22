@props(['title', 'subtitle' => '', 'reference' => null])
<div {{ $attributes->merge(['class' => 'mx-auto max-w-7xl space-y-6 font-outfit text-theme-sm text-gray-700 dark:text-gray-300']) }}>
    <header class="flex flex-col gap-3 py-1 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <svg class="h-5 w-5 stroke-current" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Zm0 0v6h6M8 13h8m-8 4h5"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white/90">{{ $title }}</h1>
            </div>
        </div>
        @if ($reference || isset($referenceActions))
            <div class="flex items-center gap-2">
                @if ($reference)
                    <span class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-theme-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $reference }}</span>
                @endif
                @isset ($referenceActions)
                    {{ $referenceActions }}
                @endisset
            </div>
        @endif
    </header>
    {{ $slot }}
</div>
