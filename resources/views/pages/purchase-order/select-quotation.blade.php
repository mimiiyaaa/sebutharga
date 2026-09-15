@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Pilih Pelanggan untuk PO')" />
<x-common.component-card :title="__('Pilih Pelanggan')">
    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Sebutharga Final terkini dipilih automatik mengikut tarikh sebutharga, kemudian versi tertinggi. Item dan harga PO diisi manual.') }}</p>
    @if ($errors->any())
        <p role="alert" class="text-error-600 dark:text-error-400">{{ __('Sila pilih pelanggan yang mempunyai sebutharga Final.') }}</p>
    @endif
    <form method="GET" action="{{ route('purchase-order.form') }}" class="space-y-5" x-data="{ customerId: '', quotations: {{ Illuminate\Support\Js::from($quotations) }}, get quotation() { return this.quotations.find(row => String(row.customer_id) === String(this.customerId)) } }">
        <label for="customer_id" class="block text-sm text-gray-700 dark:text-gray-300">{{ __('Pelanggan') }}</label>
        <select id="customer_id" name="customer_id" required x-model="customerId" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">{{ __('Pilih Pelanggan') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->customer_id }}">{{ $customer->company_name }} (ID: {{ $customer->customer_id }})</option>
            @endforeach
        </select>
        <div x-cloak x-show="quotation" class="rounded-lg bg-gray-50 p-4 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <p class="font-semibold">{{ __('Sebutharga Final Terkini') }}</p>
            @foreach (['quotation_no' => 'No. Sebut Harga', 'quotation_id' => 'ID Sebut Harga', 'quotation_detail_id' => 'ID Versi', 'draft_no' => 'Versi', 'quotation_date' => 'Tarikh', 'quotation_title' => 'Tajuk'] as $field => $label)
                <p>{{ __($label) }}: <span x-text="quotation?.{{ $field }} || '—'"></span></p>
            @endforeach
        </div>
        <p x-cloak x-show="customerId && !quotation" class="text-gray-600 dark:text-gray-400">{{ __('Pelanggan ini belum mempunyai sebutharga Final.') }}</p>
        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-order.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Kembali ke Senarai PO') }}</a>
            <button type="submit" :disabled="!quotation" class="rounded-lg bg-brand-500 px-5 py-3 text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-500 dark:text-white">{{ __('Teruskan') }}</button>
        </div>
    </form>
</x-common.component-card>
@endsection
