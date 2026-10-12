@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$invoice->invoice_no">
    <x-slot:referenceActions><a href="{{ route('invoice.customer') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali ke Senarai Invois') }}</a></x-slot:referenceActions>
    <x-common.document-card :title="__('Pratonton PDF')">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Untuk mencetak, buka PDF dan gunakan ikon cetak atau Ctrl+P dalam pembaca PDF.') }}</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('invoice.customer.pdf', $invoice->invoice_id) }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Cetak / Buka PDF') }}</a>
                <a href="{{ route('invoice.customer.pdf', ['id'=>$invoice->invoice_id, 'download'=>1]) }}" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Muat Turun PDF') }}</a>
            </div>
        </div>
        <iframe src="{{ route('invoice.customer.pdf', $invoice->invoice_id) }}" title="{{ __('Pratonton PDF') }} {{ $invoice->invoice_no }}" class="h-[1123px] w-full rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"></iframe>
    </x-common.document-card>
</x-common.document-workspace>
@endsection
