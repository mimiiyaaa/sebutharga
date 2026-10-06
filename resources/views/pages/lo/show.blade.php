@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Lihat Local Order')" />

    @php
        $documentIsImage = in_array(strtolower(pathinfo((string) $lo->document_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
        $customerName = $lo->company_name ?: ($lo->customer_name ?: '—');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white px-5 py-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Lihat Local Order') }}</h1>
                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Berjaya') }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $lo->lo_inden_no }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('lo') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
                <a href="{{ route('lo.edit', $lo->lo_inden_id) }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Edit Local Order') }}</a>
            </div>
        </div>

        <x-common.document-card :title="__('Ringkasan Local Order')" :desc="__('Maklumat utama dokumen ini.')">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('No. Local Order') }}</p>
                    <p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ $lo->lo_inden_no }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Pelanggan / Syarikat') }}</p>
                    <p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ $customerName }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tarikh Local Order') }}</p>
                    <p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ \Carbon\Carbon::parse($lo->lo_inden_date)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-500/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ __('Jumlah (RM)') }}</p>
                    <p class="mt-2 text-base font-semibold tabular-nums text-brand-700 dark:text-brand-300">RM {{ number_format((float) $lo->amount, 2) }}</p>
                </div>
            </div>
        </x-common.document-card>

        @if($lo->quotation_no)
            <x-common.document-card :title="__('Sebut Harga Final')" :desc="__('Rujukan sebut harga yang digunakan untuk Local Order ini.')">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No. Sebut Harga') }}</p>
                        <p class="mt-1.5 font-semibold text-gray-800 dark:text-white/90">{{ $lo->quotation_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Versi') }}</p>
                        <p class="mt-1.5 font-semibold text-gray-800 dark:text-white/90">{{ $lo->final_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tarikh Sebut Harga') }}</p>
                        <p class="mt-1.5 font-semibold text-gray-800 dark:text-white/90">{{ $lo->quotation_date ? \Carbon\Carbon::parse($lo->quotation_date)->format('d/m/Y') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</p>
                        <span class="mt-1.5 inline-flex rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Berjaya') }}</span>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tajuk') }}</p>
                        <p class="mt-1.5 font-semibold text-gray-800 dark:text-white/90">{{ $lo->quotation_title ?: '—' }}</p>
                    </div>
                </div>
            </x-common.document-card>
        @endif

        <x-common.document-card :title="__('Dokumen Local Order')" :desc="__('Pratonton dokumen yang telah dimuat naik.')">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-950/40">
                @if($documentIsImage)
                    <img src="{{ route('lo.document', $lo->lo_inden_id) }}" alt="{{ __('Dokumen Local Order') }}" class="mx-auto max-h-[720px] w-auto max-w-full rounded-lg object-contain">
                @else
                    <iframe src="{{ route('lo.document', $lo->lo_inden_id) }}" title="{{ __('Dokumen Local Order') }}" class="h-[720px] w-full rounded-lg border-0"></iframe>
                @endif
            </div>
        </x-common.document-card>
    </div>
@endsection
