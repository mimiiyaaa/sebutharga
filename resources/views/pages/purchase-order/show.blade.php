@extends('layouts.app')

@section('content')
<x-common.document-workspace :title="__('Butiran PO')" :reference="$order->po_no" x-data="{ loginInfoOpen: false }" @keydown.escape.window="loginInfoOpen = false">
    <x-slot:referenceActions>
        <a href="{{ route('purchase-order.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-theme-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Kembali') }}</a>
        <div class="relative" @click.outside="loginInfoOpen = false">
            <button type="button" @click="loginInfoOpen = !loginInfoOpen" title="{{ __('Maklumat Login') }}" aria-label="{{ __('Maklumat Login') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:text-brand-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </button>
            <x-common.created-by-panel :name="$order->creator_name" :jawatan="$order->creator_jawatan" :email="$order->creator_email" :created-at="$order->created_at" document-label="PO ini" />
        </div>
    </x-slot:referenceActions>

    @php
        $issuer = ['Nama Syarikat' => config('purchase_order.issuer_name'), 'Nombor Telefon' => config('purchase_order.issuer_phone'), 'E-mel' => config('purchase_order.issuer_email'), 'Alamat Syarikat' => config('purchase_order.issuer_address')];
        $supplier = ['Nama Syarikat' => $order->company_name, 'Nombor Telefon' => $order->supplier_phone, 'Untuk Perhatian' => $order->attention_supplier, 'Alamat' => $order->supplier_address];
        $termsConditions = preg_replace('/^\s*\d+\.\s*$/m', '', (string) $order->terms_conditions);
    @endphp

    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Pesanan Belian') }}</h2>
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                <a href="{{ route('purchase-order.preview', $order->purchase_order_id) }}" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Pratonton') }}</a>
                <a href="{{ route('purchase-order.edit', ['id' => $order->purchase_order_id, 'from' => 'show']) }}" class="inline-flex h-10 items-center rounded-lg bg-brand-500 px-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach (['No. PO' => $order->po_no, 'Tarikh PO' => $order->po_date ? \Carbon\Carbon::parse($order->po_date)->format('d/m/Y') : '—', 'Pembekal' => $order->company_name, 'No. Sebut Harga' => $order->quotation_reference_no, 'Kemaskini Terakhir' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y H:i') : '—', 'Status PO' => $order->status_po] as $label => $value)
                <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __($label) }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6 dark:border-gray-800 lg:grid-cols-2">
            @foreach (['Maklumat Syarikat Pengeluar' => $issuer, 'Maklumat Pembekal' => $supplier] as $section => $fields)
                <section class="border-t border-gray-100 pt-5 first:border-t-0 first:pt-0 dark:border-gray-800">
                    <h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">{{ __($section) }}</h3>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @foreach ($fields as $label => $value)
                            <div @class(['sm:col-span-2' => in_array($label, ['Alamat Syarikat', 'Alamat'])])><p class="text-sm text-gray-500 dark:text-gray-400">{{ __($label) }}</p><p class="mt-2 whitespace-pre-line break-words text-base font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
        <div class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6 dark:border-gray-800 lg:grid-cols-2">
            <section class="border-t border-gray-100 pt-5 first:border-t-0 first:pt-0 dark:border-gray-800">
                <h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Penghantaran') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div class="sm:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Alamat Penghantaran') }}</p><p class="mt-2 whitespace-pre-line break-words text-base font-medium text-gray-800 dark:text-white/90">{{ $order->delivery_address ?: '—' }}</p></div><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Untuk Perhatian') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $order->attention_delivery ?: config('purchase_order.delivery_attention') ?: '—' }}</p></div></div>
            </section>
            <section class="border-t border-gray-100 pt-5 dark:border-gray-800 lg:border-t-0 lg:border-s lg:ps-6 lg:pt-0">
                <h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Pengesahan PO') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Disediakan Oleh') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $order->prepared_by ?: '—' }}</p></div><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Diluluskan Oleh') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $order->approved_by ?: '—' }}</p></div></div>
            </section>
        </div>
        <div class="border-t border-gray-100 p-6 dark:border-gray-800"><h3 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Terma dan Syarat Pembelian') }}</h3><p class="whitespace-pre-line text-base leading-6 text-gray-700 dark:text-gray-300">{{ trim($termsConditions) ?: '—' }}</p></div>
    </section>

    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800"><div><h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Item Pesanan Belian') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Maklumat item PO ini') }}</p></div></div>
        <div class="custom-scrollbar overflow-x-auto"><table class="w-full min-w-[760px] text-start text-theme-sm"><thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white"><tr><th class="w-14 px-5 py-4 text-center text-theme-xs font-semibold">{{ __('Bil') }}</th><th class="px-5 py-4 text-start text-theme-xs font-semibold">{{ __('Perihal Barangan') }}</th><th class="px-5 py-4 text-start text-theme-xs font-semibold">{{ __('Kuantiti') }}</th><th class="px-5 py-4 text-start text-theme-xs font-semibold">{{ __('Unit') }}</th><th class="px-5 py-4 text-end text-theme-xs font-semibold">{{ __('Harga Seunit (RM)') }}</th><th class="px-5 py-4 text-end text-theme-xs font-semibold">{{ __('Jumlah (RM)') }}</th></tr></thead><tbody>@forelse ($items as $item)<tr class="border-b border-gray-100 even:bg-gray-50/60 hover:bg-brand-50/40 dark:border-gray-800 dark:even:bg-gray-800/30 dark:hover:bg-brand-500/5"><td class="px-5 py-5 text-center tabular-nums text-gray-400 dark:text-gray-500">{{ $loop->iteration }}</td><td class="px-5 py-5 font-medium leading-6 text-gray-800 dark:text-gray-200"><p class="whitespace-pre-line break-words">{{ $item->item_description }}</p></td><td class="px-5 py-5 text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td><td class="px-5 py-5 text-gray-600 dark:text-gray-300">{{ $item->unit }}</td><td class="px-5 py-5 text-end tabular-nums text-gray-600 dark:text-gray-300">RM {{ number_format((float) $item->unit_price, 2) }}</td><td class="px-5 py-5 text-end font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format((float) $item->subtotal, 2) }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada item') }}</td></tr>@endforelse</tbody></table></div>
        <div class="flex justify-end border-t border-gray-100 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-900"><div class="w-full max-w-sm space-y-2 text-theme-sm text-gray-700 dark:text-gray-300"><div class="flex justify-between"><span>{{ __('Jumlah Kasar') }}</span><span class="tabular-nums">RM {{ number_format((float) $order->gross_amount, 2) }}</span></div><div class="flex justify-between"><span>{{ __('Jumlah Diskaun') }}</span><span class="tabular-nums">RM {{ number_format((float) $order->discount_amount, 2) }}</span></div><div class="flex justify-between"><span>{{ __('SST') }} ({{ (float) $order->sst_percent }}%)</span><span class="tabular-nums">RM {{ number_format((float) $order->sst_amount, 2) }}</span></div><div class="flex justify-between border-t border-gray-200 pt-3 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white/90"><span>{{ __('Jumlah Bersih') }}</span><span class="tabular-nums">RM {{ number_format((float) $order->net_amount, 2) }}</span></div></div></div>
    </section>
</x-common.document-workspace>
@endsection
