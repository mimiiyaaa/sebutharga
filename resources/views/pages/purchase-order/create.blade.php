@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Tambah PO')" />
    <div class="mb-6 rounded-lg border border-brand-200 bg-brand-50 p-4 text-sm text-gray-800 print:hidden dark:border-brand-800 dark:bg-brand-500/10 dark:text-gray-200">
        <h2 class="font-semibold">{{ __('Rujukan Sistem — Tidak Dicetak dalam PDF') }}</h2>
        <p>{{ $quotation->company_name }} · {{ __('ID Pelanggan') }}: {{ $quotation->customer_id }}</p>
        <p>{{ $quotation->quotation_no }} · {{ __('ID Sebut Harga') }}: {{ $quotation->quotation_id }} · {{ __('Versi') }} {{ $quotation->draft_no }} · {{ __('ID Versi') }}: {{ $quotation->quotation_detail_id }}</p>
        <p>{{ $quotation->quotation_title }}</p>
    </div>
    <x-purchase-order.create-form :suppliers="$suppliers" :customers="$customers" :quotation="$quotation" />
@endsection
