@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="font-outfit" x-data="{ search: '' }">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-wrap items-center gap-2 px-6">
                <input x-model="search" type="search" placeholder="{{ __('Cari no. baucar, invois atau pelanggan') }}" aria-label="{{ __('Cari no. baucar, invois atau pelanggan') }}" class="h-11 min-w-60 flex-1 rounded-lg border border-gray-200 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <a href="{{ route('baucar-bayaran.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">+ {{ __('Tambah') }}</a>
            </div>
            <div class="space-y-5 px-6 pb-6">
                @foreach ($invoiceGroups as $group)
                    @php
                        $statusClass = match ($group->payment_status) {
                            __('Selesai Dibayar') => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                            __('Bayaran Sebahagian') => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                            __('Perlu Semakan') => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
                        };
                    @endphp
                    <section x-show="!search || @js($group->search_text).includes(search.toLowerCase())" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 bg-gray-50 px-5 py-3.5 dark:border-gray-700 dark:bg-gray-800/70">
                            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h2 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">{{ $group->invoice_no }}</h2><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $group->payment_status }}</span></div><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $group->customer_name ?: '—' }}</p></div>
                            <div class="grid w-full grid-cols-3 gap-x-6 gap-y-1 text-end text-xs text-gray-500 dark:text-gray-400 sm:w-auto sm:min-w-[25rem]"><span class="whitespace-nowrap">{{ __('Invoice') }}: <strong class="tabular-nums text-gray-700 dark:text-gray-200">RM {{ number_format($group->total_amount, 2) }}</strong></span><span class="whitespace-nowrap">{{ __('Dibayar') }}: <strong class="tabular-nums text-success-600 dark:text-success-400">RM {{ number_format($group->paid_amount, 2) }}</strong></span><span class="whitespace-nowrap">{{ __('Baki') }}: <strong class="tabular-nums text-gray-700 dark:text-gray-200">RM {{ number_format($group->balance_amount, 2) }}</strong></span></div>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($group->payments as $payment)
                                <div class="flex flex-wrap items-center gap-4 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                    <div class="flex min-w-0 flex-1 items-center gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"><svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M6 3.75h9L18 6.8v13.45H6V3.75Zm8.5 0v3.5H18M8.5 11h7M8.5 14.5h7M8.5 8h1"/></svg></div><div class="min-w-0"><p class="truncate font-semibold text-gray-800 dark:text-white/90">{{ $payment->voucher_no }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($payment->voucher_date)->format('d/m/Y') }} · {{ $payment->payment_method ?: '—' }}</p></div></div>
                                    <div class="min-w-32 text-start sm:text-end"><p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Bayaran Ini') }}</p><p class="mt-1 font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ $payment->allocation_amount === null ? __('Perlu Semakan') : 'RM '.number_format((float) $payment->allocation_amount, 2) }}</p></div>
                                    <div class="document-actions"><a href="{{ $payment->voucher_url }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ $payment->delete_url }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini? Baki invois akan dikira semula.')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l-1-13M10 11v5m4-5v5"/></svg></button></form></div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
                @foreach ($unlinkedDocuments as $document)
                    <section x-show="!search || @js(strtolower($document->voucher_no . ' ' . $document->payee_name)).includes(search.toLowerCase())" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/40"><div class="border-b border-gray-200 bg-gray-50 px-5 py-3.5 dark:border-gray-700 dark:bg-gray-800/70"><h2 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">{{ __('Tanpa Invoice') }}</h2></div><div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4"><div><p class="font-semibold text-gray-800 dark:text-white/90">{{ $document->voucher_no }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $document->payee_name ?: '—' }} · {{ \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y') }} · {{ $document->payment_method ?: '—' }}</p></div><div class="flex items-center gap-4"><p class="font-semibold tabular-nums text-gray-800 dark:text-white/90">RM {{ number_format((float) $document->total_amount, 2) }}</p><div class="document-actions"><a href="{{ route('baucar-bayaran.show', $document->id) }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ route('baucar-bayaran.delete', $document->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini?')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l-1-13M10 11v5m4-5v5"/></svg></button></form></div></div></div></section>
                @endforeach
                @if ($invoiceGroups->isEmpty() && $unlinkedDocuments->isEmpty())<div class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('Tiada baucar bayaran untuk dipaparkan.') }}</div>@endif
            </div>
            <div class="hidden min-w-0 max-w-full overflow-x-auto px-6 pb-6">
                <table class="w-full min-w-[1180px] text-sm text-gray-700 dark:text-gray-300">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/60"><tr><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('No. Invoice') }}</th><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Pelanggan') }}</th><th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Jumlah Invoice') }}</th><th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Dibayar') }}</th><th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Baki') }}</th><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('No. Baucar') }}</th><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</th><th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Cara Bayaran') }}</th><th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Bayaran Ini') }}</th><th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tindakan') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($invoiceGroups as $group)
                            @php
                                $statusClass = match ($group->payment_status) {
                                    __('Selesai Dibayar') => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                                    __('Bayaran Sebahagian') => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                                    __('Perlu Semakan') => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
                                    default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
                                };
                            @endphp
                            @foreach ($group->payments as $payment)
                                <tr x-show="!search || @js($group->search_text).includes(search.toLowerCase())" class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"><td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white/90">{{ $group->invoice_no }}</td><td class="px-4 py-3.5 font-medium">{{ $group->customer_name ?: '—' }}</td><td class="px-4 py-3.5 text-end tabular-nums">RM {{ number_format($group->total_amount, 2) }}</td><td class="px-4 py-3.5 text-end font-medium tabular-nums text-success-600 dark:text-success-400">RM {{ number_format($group->paid_amount, 2) }}</td><td class="px-4 py-3.5 text-end tabular-nums">RM {{ number_format($group->balance_amount, 2) }}</td><td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $group->payment_status }}</span></td><td class="px-4 py-3.5 font-medium">{{ $payment->voucher_no }}</td><td class="px-4 py-3.5">{{ \Carbon\Carbon::parse($payment->voucher_date)->format('d/m/Y') }}</td><td class="px-4 py-3.5">{{ $payment->payment_method ?: '—' }}</td><td class="px-4 py-3.5 text-end font-medium tabular-nums">{{ $payment->allocation_amount === null ? __('Perlu Semakan') : 'RM '.number_format((float) $payment->allocation_amount, 2) }}</td><td class="px-4 py-3.5"><div class="document-actions"><a href="{{ $payment->voucher_url }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ $payment->delete_url }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini? Baki invois akan dikira semula.')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l-1-13M10 11v5m4-5v5"/></svg></button></form></div></td></tr>
                            @endforeach
                        @endforeach
                        @foreach ($unlinkedDocuments as $document)
                            <tr x-show="!search || @js(strtolower($document->voucher_no . ' ' . $document->payee_name)).includes(search.toLowerCase())" class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"><td class="px-4 py-3.5">—</td><td class="px-4 py-3.5 font-medium">{{ $document->payee_name ?: '—' }}</td><td colspan="4" class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ __('Tanpa Invoice') }}</td><td class="px-4 py-3.5 font-medium">{{ $document->voucher_no }}</td><td class="px-4 py-3.5">{{ \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y') }}</td><td class="px-4 py-3.5">{{ $document->payment_method ?: '—' }}</td><td class="px-4 py-3.5 text-end font-medium tabular-nums">RM {{ number_format((float) $document->total_amount, 2) }}</td><td class="px-4 py-3.5"><div class="document-actions"><a href="{{ route('baucar-bayaran.show', $document->id) }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ route('baucar-bayaran.delete', $document->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini?')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button></form></div></td></tr>
                        @endforeach
                        @if ($invoiceGroups->isEmpty() && $unlinkedDocuments->isEmpty())
                            <tr><td colspan="11" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada baucar bayaran untuk dipaparkan.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="hidden">
                @foreach ($invoiceGroups as $group)
                    @php
                        $statusClass = match ($group->payment_status) {
                            __('Selesai Dibayar') => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                            __('Bayaran Sebahagian') => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                            __('Perlu Semakan') => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
                        };
                    @endphp
                    <section x-show="!search || @js($group->search_text).includes(search.toLowerCase())" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/40">
                        <div class="border-b border-gray-100 bg-gray-50/80 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2"><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $group->invoice_no }}</h2><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $group->payment_status }}</span></div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $group->customer_name ?: '—' }} · {{ $group->payments->count() }} {{ __('baucar bayaran') }}</p>
                                </div>
                                <div class="grid min-w-full grid-cols-3 gap-3 sm:min-w-0 sm:gap-6">
                                    <div><p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Jumlah Invoice') }}</p><p class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format($group->total_amount, 2) }}</p></div>
                                    <div><p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Dibayar') }}</p><p class="mt-1 font-semibold tabular-nums text-success-600 dark:text-success-400">RM {{ number_format($group->paid_amount, 2) }}</p></div>
                                    <div><p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Baki') }}</p><p class="mt-1 font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format($group->balance_amount, 2) }}</p></div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-sm text-gray-700 dark:text-gray-300">
                                <thead class="border-b border-gray-100 dark:border-gray-700"><tr><th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('No. Baucar') }}</th><th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</th><th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Cara Bayaran') }}</th><th class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Bayaran Ini') }}</th><th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tindakan') }}</th></tr></thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($group->payments as $payment)
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"><td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white/90">{{ $payment->voucher_no }}</td><td class="px-5 py-3.5">{{ \Carbon\Carbon::parse($payment->voucher_date)->format('d/m/Y') }}</td><td class="px-5 py-3.5">{{ $payment->payment_method ?: '—' }}</td><td class="px-5 py-3.5 text-end font-medium tabular-nums">{{ $payment->allocation_amount === null ? __('Perlu Semakan') : 'RM '.number_format((float) $payment->allocation_amount, 2) }}</td><td class="px-5 py-3.5"><div class="document-actions"><a href="{{ $payment->voucher_url }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ $payment->delete_url }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini? Baki invois akan dikira semula.')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button></form></div></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach

                @foreach ($unlinkedDocuments as $document)
                    <section x-show="!search || @js(strtolower($document->voucher_no . ' ' . $document->payee_name)).includes(search.toLowerCase())" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4"><div><h2 class="font-semibold text-gray-900 dark:text-white/90">{{ __('Tanpa Invoice') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $document->payee_name ?: '—' }} · {{ \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y') }}</p></div><div class="document-actions"><a href="{{ route('baucar-bayaran.show', $document->id) }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ route('baucar-bayaran.delete', $document->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini?')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l-1-13M10 11v5"/></svg></button></form></div></div>
                        <div class="border-t border-gray-100 px-5 py-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $document->voucher_no }} · {{ $document->payment_method ?: '—' }} · <span class="font-semibold tabular-nums">RM {{ number_format((float) $document->total_amount, 2) }}</span></div>
                    </section>
                @endforeach

                @if ($invoiceGroups->isEmpty() && $unlinkedDocuments->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('Tiada baucar bayaran untuk dipaparkan.') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
