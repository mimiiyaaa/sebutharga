@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <a href="{{ route($backRoute) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
    </div>

    <div class="space-y-6 font-outfit">
        <x-common.document-card :title="$documentType" :desc="__('Masukkan maklumat asas dokumen sebelum item ditambah.')">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><label for="document_no" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $documentNumberLabel }}</label><input id="document_no" type="text" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                <div><label for="document_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh') }}</label><input id="document_date" type="date" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                <div class="md:col-span-2"><label for="customer_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Pelanggan / Syarikat') }}</label><input id="customer_name" type="text" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
            </div>
        </x-common.document-card>

        <x-common.document-card :title="__('Item')" :desc="__('Senarai item akan dilengkapkan selepas contoh dokumen disahkan.')">
            <div class="flex min-h-44 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/40 dark:text-gray-400">{{ __('Ruang item dokumen akan disediakan di sini.') }}</div>
        </x-common.document-card>
    </div>
@endsection
