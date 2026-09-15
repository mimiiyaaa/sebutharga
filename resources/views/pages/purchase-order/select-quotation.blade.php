@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Pilih Sebut Harga untuk PO')" />
    <x-common.component-card :title="__('Pilih Sebut Harga dan Versi')">
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Pilih versi yang telah dipersetujui di luar sistem. Item dan harga PO akan diisi secara manual pada langkah seterusnya.') }}</p>
        @if ($errors->any())
            <p role="alert" class="text-sm text-error-600 dark:text-error-400">{{ __('Sila pilih sebutharga dan versi yang sah.') }}</p>
        @endif
        <form method="GET" action="{{ route('purchase-order.form') }}" class="space-y-5"
            x-data="{ quotationId: '', detailId: '', details: {{ Illuminate\Support\Js::from($details) }}, get versions() { return this.details.filter(row => String(row.quotation_id) === String(this.quotationId)) } }">
            <div>
                <label for="quotation_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sebut Harga') }}</label>
                <select id="quotation_id" name="quotation_id" x-model="quotationId" @change="detailId = ''" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">{{ __('Pilih Sebut Harga') }}</option>
                    @foreach ($quotations as $quotation)
                        <option value="{{ $quotation->quotation_id }}">{{ $quotation->quotation_no }} — {{ $quotation->quotation_date }} — {{ $quotation->quotation_title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quotation_detail_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Versi Sebut Harga') }}</label>
                <select id="quotation_detail_id" name="quotation_detail_id" x-model="detailId" :disabled="!quotationId || versions.length === 0" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">{{ __('Pilih Versi') }}</option>
                    <template x-for="detail in versions" :key="detail.quotation_detail_id">
                        <option :value="detail.quotation_detail_id" x-text="{{ Illuminate\Support\Js::from(__('Versi')) }} + ' ' + detail.draft_no"></option>
                    </template>
                </select>
                <p x-cloak x-show="quotationId && versions.length === 0" class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Sebutharga ini belum mempunyai versi.') }}</p>
            </div>
            @if ($quotations->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Belum ada rekod sebutharga dalam sistem.') }}</p>
            @endif
            <input type="hidden" name="preview" :value="quotationId && detailId ? 0 : 1">
            <div class="flex justify-end gap-3">
                <a href="{{ route('purchase-order.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Kembali ke Senarai PO') }}</a>
                <button type="submit"  class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Teruskan') }}</button>
            </div>
        </form>
    </x-common.component-card>
@endsection
