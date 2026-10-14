@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$order->po_no">
        <x-slot:actions>
            <a href="{{ route('purchase-order.show', $order->purchase_order_id) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a>
        </x-slot:actions>
    </x-common.page-breadcrumb>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('purchase-order.pdf', $order->purchase_order_id) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg bg-brand-500 font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Muat Turun PDF') }}</a>
        </div>
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
            <iframe title="{{ $order->po_no }}" sandbox class="mx-auto block h-[1123px] w-[794px] border-0 bg-white shadow-theme-sm dark:bg-white" srcdoc="{{ view('pages.purchase-order.pdf', ['order' => $order, 'items' => $items, 'document' => $document])->render() }}"></iframe>
        </div>
    </div>
@endsection
