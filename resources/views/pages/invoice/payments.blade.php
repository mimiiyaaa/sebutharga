@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="__('Bayaran Invois')" :reference="$invoice->invoice_no">
    <x-slot:referenceActions>
        <a href="{{ route('invoice.customer') }}" class="document-action">{{ __('Kembali') }}</a>
        <a href="{{ route('invoice.customer.show', $invoice->invoice_id) }}" class="document-action">{{ __('Lihat Invois') }}</a>
    </x-slot:referenceActions>
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pelanggan') }}</p>
        <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $document['customer_name'] ?? '—' }}</h2>
        @if ($invoice->invoice_title)<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $invoice->invoice_title }}</p>@endif
    </div>
    <x-common.invoice-payments :invoice="$invoice" :payments="$payments" />
</x-common.document-workspace>
@endsection
