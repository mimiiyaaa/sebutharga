@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Pratonton Dokumen')">
<x-slot:actions><a href="{{ (request('from') === 'final' && request('via') !== 'detail' ? route('sebut-harga.final') : route('sebut-harga.edit', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id, 'from' => request('from') === 'final' ? 'final' : 'draft'])) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __(request('from') === 'final' && request('via') !== 'detail' ? 'Kembali ke Final Sebut Harga' : 'Kembali ke Maklumat Sebut Harga') }}</a></x-slot:actions>
</x-common.page-breadcrumb>
<div class="mb-6 flex flex-wrap justify-end gap-3">
<a href="{{ route('sebut-harga.pdf', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id]) }}" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500">{{ __('Muat Turun PDF') }}</a>
</div>
<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
<iframe title="{{ $quotation->quotation_no }}" sandbox class="mx-auto block h-[1123px] w-[794px] border-0 bg-white shadow-theme-sm dark:bg-white" srcdoc="{{ view('pages.sebut-harga.pdf', ['quotation' => $quotation, 'detail' => $draft, 'items' => $draft->items])->render() }}"></iframe>
</div>
@endsection
