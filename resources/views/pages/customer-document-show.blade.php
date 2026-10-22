@extends('layouts.app')
@section('content')
@php
    $isInvoice = $kind === 'invoice';
    $record = $isInvoice ? $invoice : $order;
    $id = $isInvoice ? $record->invoice_id : $record->delivery_order_id;
    $number = $isInvoice ? $record->invoice_no : $record->do_no;
    $date = $isInvoice ? $record->invoice_date : $record->do_date;
    $routePrefix = $kind.'.customer';
    $sections = [
        __('Maklumat Syarikat') => ['issuer_name' => __('Nama Syarikat'), 'issuer_address' => __('Alamat Syarikat'), 'issuer_phone' => __('Nombor Telefon'), 'issuer_email' => __('E-mel')],
        __('Maklumat Pelanggan') => ['customer_name' => __('Pelanggan / Syarikat'), 'customer_address' => __('Alamat Pelanggan')],
    ];
    $footerSections = $isInvoice ? [
        __('Arahan Pembayaran') => ['payment_instructions' => __('Arahan Pembayaran'), 'bank_name' => __('Nama Bank'), 'account_name' => __('Nama Akaun'), 'account_no' => __('No. Akaun')],
        __('Disediakan oleh,') => ['prepared_by' => __('Nama Penyedia (pilihan)'), 'prepared_role' => __('Jawatan / Unit'), 'signature_company' => __('Nama Syarikat')],
    ] : [
        __('Nota Penghantaran') => ['notes' => __('Nota'), 'delivered_by' => __('Nama Penghantar (pilihan)'), 'signature_company' => __('Nama Syarikat')],
        __('Diterima dalam keadaan baik oleh,') => ['recipient_name' => __('Nama Penerima (pilihan)'), 'received_date' => __('Tarikh Penerimaan (pilihan)')],
    ];
@endphp
<x-common.document-workspace :title="$title">
    <x-slot:referenceActions>
        <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route($routePrefix) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a>
        <a href="{{ route($routePrefix.'.edit', $id) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg bg-brand-500 font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Edit') }}</a>
        <a href="{{ route($routePrefix.'.preview', $id) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-400">{{ __('Pratonton PDF') }}</a>
        <form method="POST" action="{{ route($routePrefix.'.delete', $id) }}" x-data @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam dokumen ini? Tindakan ini tidak boleh dibatalkan.')) })">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-error-300 bg-error-50 text-error-700 dark:border-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Padam') }}</button>
        </form>
        <x-common.document-creator :creator-id="$record->created_by" :created-at="$record->created_at" :document-label="$isInvoice ? __('invois ini') : __('DO ini')" />
            </div>
    </x-slot:referenceActions>
    <div class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-7 dark:border-gray-800 sm:px-8">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">{{ $isInvoice ? __('Maklumat Invois') : __('Maklumat Delivery Order') }}</p>
                    <h2 class="mt-2 truncate text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">{{ $number }}</h2>
                    @if($isInvoice && !empty($invoice->invoice_title))<p class="mt-2 text-base font-medium text-gray-700 dark:text-gray-300">{{ $invoice->invoice_title }}</p>@endif
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $document['customer_name'] ?? $record->recipient_name ?? '—' }} · {{ $isInvoice ? __('Ringkasan invois') : __('Ringkasan delivery order') }}</p>
                </div>
            </div>
            <dl class="grid grid-cols-1 gap-4 border-b border-gray-100 p-6 text-sm sm:grid-cols-2 dark:border-gray-800">
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('No. Dokumen') }}</dt><dd class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $number }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</dt><dd class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('No. Local Order (LO)') }}</dt><dd class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $document['lo_reference'] ?? '—' }}</dd></div>
            </dl>
        </section>
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($sections as $section => $fields)
                <x-common.document-card :title="$section">
                    @include('pages.customer-document-fields', ['fields' => $fields])
                </x-common.document-card>
            @endforeach
        </div>
        <x-common.document-card :title="__('Senarai Item')">
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full overflow-hidden rounded-xl text-start text-sm">
                    <thead class="overflow-hidden rounded-xl bg-error-800 text-white dark:bg-error-900 dark:text-white"><tr>
                        @foreach([__('Bil'), __('Keterangan'), __('Kuantiti'), __('Unit')] as $heading)<th class="px-4 py-3 text-start">{{ $heading }}</th>@endforeach
                        @if($isInvoice)<th class="px-4 py-3 text-end">{{ __('Harga Seunit (RM)') }}</th><th class="px-4 py-3 text-end">{{ __('Jumlah (RM)') }}</th>@else<th class="px-4 py-3 text-center">{{ __('Semakan') }}</th>@endif
                    </tr></thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-4">{{ $loop->iteration }}</td><td class="min-w-64 whitespace-pre-line break-words px-4 py-4">{{ $item->item_description }}</td>
                                <td class="px-4 py-4 tabular-nums">{{ number_format($item->quantity, 2) }}</td><td class="px-4 py-4">{{ $item->unit }}</td>
                                @if($isInvoice)<td class="px-4 py-4 text-end tabular-nums">{{ number_format($item->unit_price, 2) }}</td><td class="px-4 py-4 text-end tabular-nums">{{ number_format($item->subtotal, 2) }}</td>@else<td class="px-4 py-4 text-center">{{ $item->remarks === '/' ? '✓' : '—' }}</td>@endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="rounded-xl bg-brand-50 p-5 text-end font-semibold text-brand-800 dark:bg-brand-500/10 dark:text-brand-300">{{ $isInvoice ? __('Jumlah (RM)') : __('Jumlah Kuantiti') }}: {{ number_format($isInvoice ? $invoice->total_amount : $items->sum('quantity'), 2) }}</div>
        </x-common.document-card>
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($footerSections as $section => $fields)
                <x-common.document-card :title="$section">
                    @include('pages.customer-document-fields', ['fields' => $fields])
                </x-common.document-card>
            @endforeach
        </div>
    </div>
</x-common.document-workspace>
@endsection
