@props(['label', 'value', 'hint' => null, 'href' => null, 'tone' => 'brand', 'amounts' => []])
@php
$colors = match ($tone) {
    'purple' => 'border-theme-purple-500/30 bg-theme-purple-500/10 text-theme-purple-500 dark:border-theme-purple-500/40 dark:bg-theme-purple-500/15 dark:text-white/90',
    'success' => 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400',
    'warning' => 'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-400',
    'error' => 'border-error-200 bg-error-50 text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400',
    default => 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300',
};
@endphp
<div class="rounded-2xl border p-5 {{ $colors }}">
    <p class="text-sm font-medium">{{ __($label) }}</p>
    <p class="mt-3 text-2xl font-semibold tabular-nums">{{ $value }}</p>
    @if ($hint)<p class="mt-2 text-xs">{{ __($hint) }}</p>@endif
    @if ($amounts)
        <dl class="mt-4 space-y-2 border-t border-current/15 pt-3 text-sm">
            @foreach ($amounts as $amountLabel => $amount)
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <dt>{{ __($amountLabel) }}</dt>
                    <dd class="font-semibold tabular-nums">RM {{ number_format($amount, 2) }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
    @if ($href)<a class="mt-3 inline-block text-sm font-medium underline underline-offset-4" href="{{ $href }}">{{ __('Lihat') }}</a>@endif
</div>
