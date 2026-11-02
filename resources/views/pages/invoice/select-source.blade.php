@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Pilih Rujukan Invoice')" :subtitle="__('Pilih LO, PO atau DO sebagai rujukan invoice')">
        <x-slot:referenceActions><a href="{{ route('invoice.customer') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <div class="space-y-6" x-data="{ tab: 'lo' }">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    @foreach(['lo' => ['LO', 'bg-blue-light-600 hover:bg-blue-light-700 dark:bg-blue-light-600 dark:hover:bg-blue-light-700'], 'po' => ['PO', 'bg-brand-500 hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600'], 'do' => ['DO', 'bg-brand-700 hover:bg-brand-800 dark:bg-brand-700 dark:hover:bg-brand-800']] as $key => [$label, $tone])
                        <button type="button" @click="tab = @js($key)" :aria-pressed="tab === @js($key)" :class="tab === @js($key) ? 'ring-2 ring-brand-300 ring-offset-2 dark:ring-brand-400 dark:ring-offset-gray-900' : ''" class="inline-flex h-10 min-w-20 items-center justify-center rounded-lg px-5 text-sm font-semibold text-white shadow-theme-xs transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 {{ $tone }}">{{ __($label) }}</button>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('invoice.customer.form') }}">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-blue-light-200 bg-blue-light-50 px-5 text-sm font-semibold text-blue-light-700 shadow-theme-xs transition hover:bg-blue-light-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-blue-light-800 dark:bg-blue-light-500/10 dark:text-blue-light-300 dark:hover:bg-blue-light-500/20">{{ __('Teruskan Tanpa Rujukan') }}</button>
                </form>
            </div>

            <section x-show="tab === 'lo'" x-cloak class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Local Order (LO)') }}</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-start text-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr><th class="px-5 py-3 text-start">{{ __('No. Dokumen') }}</th><th class="px-5 py-3 text-start">{{ __('Pelanggan') }}</th><th class="px-5 py-3 text-start">{{ __('Tarikh') }}</th><th class="px-5 py-3 text-end">{{ __('Tindakan') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($localOrders as $row)<tr><td class="px-5 py-3 font-medium">{{ $row->lo_inden_no }}</td><td class="px-5 py-3">{{ $row->company_name ?: $row->customer_name }}</td><td class="px-5 py-3">{{ $row->lo_inden_date }}</td><td class="px-5 py-3 text-end"><a class="document-action document-action-primary" href="{{ route('invoice.customer.form', ['source_type' => 'lo', 'source_id' => $row->lo_inden_id]) }}">{{ __('Pilih') }}</a></td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">{{ __('Tiada data') }}</td></tr>@endforelse</tbody></table></div>
            </section>

            <section x-show="tab === 'po'" x-cloak class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Purchase Order (PO)') }}</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-start text-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr><th class="px-5 py-3 text-start">{{ __('No. Dokumen') }}</th><th class="px-5 py-3 text-start">{{ __('Pembekal') }}</th><th class="px-5 py-3 text-start">{{ __('Tarikh') }}</th><th class="px-5 py-3 text-end">{{ __('Tindakan') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($purchaseOrders as $row)<tr><td class="px-5 py-3 font-medium">{{ $row->po_no }}</td><td class="px-5 py-3">{{ $row->company_name ?: '—' }}</td><td class="px-5 py-3">{{ $row->po_date }}</td><td class="px-5 py-3 text-end"><a class="document-action document-action-primary" href="{{ route('invoice.customer.form', ['source_type' => 'po', 'source_id' => $row->purchase_order_id]) }}">{{ __('Pilih') }}</a></td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">{{ __('Tiada data') }}</td></tr>@endforelse</tbody></table></div>
            </section>

            <section x-show="tab === 'do'" x-cloak class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Delivery Order (DO)') }}</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-start text-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr><th class="px-5 py-3 text-start">{{ __('No. Dokumen') }}</th><th class="px-5 py-3 text-start">{{ __('Pelanggan') }}</th><th class="px-5 py-3 text-start">{{ __('Tarikh') }}</th><th class="px-5 py-3 text-end">{{ __('Tindakan') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($deliveryOrders as $row)<tr><td class="px-5 py-3 font-medium">{{ $row->do_no }}</td><td class="px-5 py-3">{{ $row->company_name ?: $row->customer_name }}</td><td class="px-5 py-3">{{ $row->do_date }}</td><td class="px-5 py-3 text-end"><a class="document-action document-action-primary" href="{{ route('invoice.customer.form', ['source_type' => 'do', 'source_id' => $row->delivery_order_id]) }}">{{ __('Pilih') }}</a></td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">{{ __('Tiada data') }}</td></tr>@endforelse</tbody></table></div>
            </section>
        </div>
    </x-common.document-workspace>
@endsection
