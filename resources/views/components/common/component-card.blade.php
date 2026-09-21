@props([
    'title' => null,
    'desc' => '',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    <!-- Card Header -->
    @if($title || $desc || isset($header))
        <div class="flex items-center justify-between gap-4 px-6 py-5">
            <div>
                @if($title)
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ $title }}
                    </h3>
                @endif
                @if($desc)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $desc }}
                    </p>
                @endif
            </div>
            @isset($header)
                <div class="shrink-0">
                    {{ $header }}
                </div>
            @endisset
        </div>
    @endif

    <!-- Card Body -->
    <div class="p-4 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
