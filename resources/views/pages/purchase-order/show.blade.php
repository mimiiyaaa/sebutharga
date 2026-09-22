@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Butiran PO" />
<div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 text-gray-800 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-200">
    <h2 class="text-xl font-semibold">{{ $order->po_no }}</h2>
    <p>{{ __('No. Rujukan Sebut Harga') }}: {{ $order->quotation_reference_no }}</p>
    <p>{{ __('Pembekal') }}: {{ $order->company_name }}</p>
    <p>{{ __('Tarikh') }}: {{ $order->po_date }}</p>
    <p>{{ __('Alamat Penghantaran') }}: {{ $order->delivery_address }}</p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr>@foreach(['Perihal Barangan','Kuantiti','Unit','Harga Seunit (RM)','Jumlah (RM)'] as $label)<th class="p-3 text-start">{{ __($label) }}</th>@endforeach</tr></thead>
            <tbody>@foreach($items as $item)<tr class="border-t border-gray-200 dark:border-gray-700"><td class="p-3">{{ $item->item_description }}</td><td class="p-3">{{ $item->quantity }}</td><td class="p-3">{{ $item->unit }}</td><td class="p-3">{{ number_format($item->unit_price,2) }}</td><td class="p-3">{{ number_format($item->subtotal,2) }}</td></tr>@endforeach</tbody>
        </table>
    </div>
    <p>{{ __('Jumlah Kasar') }}: RM {{ number_format($order->gross_amount,2) }}</p>
    <p>{{ __('Jumlah Diskaun') }}: RM {{ number_format($order->discount_amount,2) }}</p>
    <p>{{ __('SST') }} ({{ (float) $order->sst_percent }}%): RM {{ number_format($order->sst_amount,2) }}</p>
    <p class="font-semibold">{{ __('Jumlah Bersih') }}: RM {{ number_format($order->net_amount,2) }}</p>
    <p class="whitespace-pre-line">{{ $order->terms_conditions }}</p>
    <p>{{ __('Disediakan Oleh') }}: {{ $order->prepared_by }}</p>
    <p>{{ __('Diluluskan Oleh') }}: {{ $order->approved_by }}</p>
    <a href="{{ route('purchase-order.index') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2 text-white dark:bg-brand-500 dark:text-white">{{ __('Kembali ke Senarai PO') }}</a>
</div>
@endsection
