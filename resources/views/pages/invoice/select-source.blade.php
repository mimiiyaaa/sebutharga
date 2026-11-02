@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Pilih Rujukan Invoice')" :subtitle="__('Pilih LO, PO atau DO sebagai rujukan invoice')">
        <x-slot:referenceActions><a href="{{ route('invoice.customer') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <div class="space-y-6" x-data="{ tab: 'lo' }">
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex w-fit rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-900">
                    <button type="button" @click="tab = 'lo'" :class="tab === 'lo' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ __('LO') }}</button>
                    <button type="button" @click="tab = 'po'" :class="tab === 'po' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ __('PO') }}</button>
                    <button type="button" @click="tab = 'do'" :class="tab === 'do' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ __('DO') }}</button>
                </div>
                <a href="{{ route('invoice.customer.form') }}" class="text-xs font-medium text-gray-600 underline dark:text-gray-300">{{ __('Teruskan Tanpa Rujukan') }}</a>
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
