@extends('layouts.app')
@section('content')
    <x-common.document-workspace :title="__('Edit').' '.$order->po_no">
    <x-slot:referenceActions><a href="{{ $returnToDetail ? route('purchase-order.show', $order->purchase_order_id) : route('purchase-order.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
    <x-purchase-order.create-form :suppliers="$suppliers" :quotation="$order" :nextPoNo="$order->po_no" :order="$order" :initial-items="$items" :return-to-detail="$returnToDetail" :show-back="false" />
    </x-common.document-workspace>
@endsection
