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
        <a href="{{ route($routePrefix) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a>
        <a href="{{ route($routePrefix.'.edit', $id) }}" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Edit') }}</a>
        <a href="{{ route($routePrefix.'.preview', $id) }}" class="rounded-lg border border-brand-300 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-400">{{ __('Pratonton PDF') }}</a>
        <form method="POST" action="{{ route($routePrefix.'.delete', $id) }}" x-data @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam dokumen ini? Tindakan ini tidak boleh dibatalkan.')) })">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-lg border border-error-300 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Padam') }}</button>
        </form>
            </div>
    </x-slot:referenceActions>
    <div class="space-y-6">
        <x-common.document-card :title="$number">
            <dl class="grid gap-6 text-sm sm:grid-cols-3">
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('No. Dokumen') }}</dt><dd class="mt-2 font-semibold">{{ $number }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</dt><dd class="mt-2 font-semibold">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">{{ __('No. Local Order (LO)') }}</dt><dd class="mt-2 font-semibold">{{ $document['lo_reference'] ?? '—' }}</dd></div>
            </dl>
        </x-common.document-card>
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($sections as $section => $fields)
                <x-common.document-card :title="$section">
                    @include('pages.customer-document-fields', ['fields' => $fields])
                </x-common.document-card>
            @endforeach
        </div>
        <x-common.document-card :title="__('Senarai Item')">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300"><tr>
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
