@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Tambah PO')" :subtitle="__('Lengkapkan maklumat pembekal, penghantaran dan item pesanan.')">
    <x-slot:referenceActions><a href="{{ route('purchase-order.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
    <x-purchase-order.create-form :suppliers="$suppliers" :quotation="$quotation" :nextPoNo="$nextPoNo" :initial-items="$initialItems" :show-back="false" />
    </x-common.document-workspace>
@endsection
