@extends('layouts.app')
@section('content')
    <x-common.document-workspace :title="__('Tambah Sebut Harga')" :subtitle="__('Lengkapkan maklumat pelanggan, item dan terma dokumen.')">
        <x-slot:referenceActions>
            <a href="{{ route('sebut-harga') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>
        <x-sebut-harga.create-form :customers="$customers" :companies="$companies" :supplier-quotations="$supplierQuotations" :quotation="$sourceQuotation" :draft="$sourceDraft" :editing="$sourceDraft !== null" :copy-as-new-quotation="$sourceDraft !== null" :show-back="false" :jawatan-options="$jawatanOptions ?? collect()" />
    </x-common.document-workspace>
@endsection
