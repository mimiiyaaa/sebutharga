@extends('layouts.app')

@section('content')
    @php
        $documentIsImage = in_array(strtolower(pathinfo((string) $lo->document_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
        $customerName = $lo->company_name ?: ($lo->customer_name ?: '—');
    @endphp

    <x-common.document-workspace :title="__('Butiran Local Order')" x-data="{ loginInfoOpen: false }" @keydown.escape.window="loginInfoOpen = false">
        <x-slot:referenceActions>
            <a href="{{ route('lo') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-theme-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Kembali') }}</a>
            <a href="{{ route('lo.edit', $lo->lo_inden_id) }}" class="inline-flex h-9 items-center justify-center rounded-lg bg-brand-500 px-3 text-theme-xs font-medium text-white transition hover:bg-brand-600">{{ __('Edit Local Order') }}</a>
            <x-common.document-creator :creator-id="$lo->created_by" :created-at="$lo->created_at" :document-label="__('LO ini')" />
        </x-slot:referenceActions>

        <div class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-7 dark:border-gray-800 sm:px-8">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">{{ __('Maklumat Local Order') }}</p>
                        <h2 class="mt-2 truncate text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">{{ $lo->lo_inden_no }}</h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $customerName }} · {{ __('Ringkasan local order') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 border-b border-gray-100 p-6 sm:grid-cols-2 dark:border-gray-800">
                    @foreach ([__('No. Local Order') => $lo->lo_inden_no, __('Tarikh Local Order') => \Carbon\Carbon::parse($lo->lo_inden_date)->format('d/m/Y'), __('Pelanggan / Syarikat') => $customerName, __('Jumlah (RM)') => 'RM ' . number_format((float) $lo->amount, 2), __('Kemaskini Terakhir') => $lo->updated_at ? \Carbon\Carbon::parse($lo->updated_at)->format('d/m/Y H:i') : '—', __('Status') => $lo->quotation_status === 1 ? __('Berjaya') : __('Menunggu') ] as $label => $value)
                        <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>
                    @endforeach
                </div>
            </section>

            @if($lo->quotation_no)
                <section class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800 sm:p-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">{{ __('Rujukan Sebut Harga') }}</p>
                        <h2 class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Sebut Harga Final') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Rujukan sebut harga yang digunakan untuk Local Order ini.') }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([__('No. Sebut Harga') => $lo->quotation_no, __('Versi') => $lo->final_no, __('Tarikh Sebut Harga') => $lo->quotation_date ? \Carbon\Carbon::parse($lo->quotation_date)->format('d/m/Y') : '—', __('Status') => __('Berjaya'), __('Tajuk') => $lo->quotation_title ?: '—'] as $label => $value)
                            <div @class(['sm:col-span-2 lg:col-span-4' => $label === __('Tajuk')])><p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>
                        @endforeach
                    </div>
                    @if($lo->quotation_id && $lo->quotation_detail_id)
                        <div class="mt-6 flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800">
                            <a href="{{ route('sebut-harga.preview', ['id' => $lo->quotation_id, 'draft' => $lo->quotation_detail_id]) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white transition hover:bg-brand-600">{{ __('Lihat Detail Sebut Harga') }}<span aria-hidden="true">→</span></a>
                        </div>
                    @endif
                </section>
            @endif

            <section class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800 sm:p-8">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Dokumen Local Order') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pratonton dokumen yang telah dimuat naik.') }}</p>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-950/40">
                    @if($documentIsImage)
                        <img src="{{ route('lo.document', $lo->lo_inden_id) }}" alt="{{ __('Dokumen Local Order') }}" class="mx-auto max-h-[720px] w-auto max-w-full rounded-lg object-contain">
                    @else
                        <iframe src="{{ route('lo.document', $lo->lo_inden_id) }}" title="{{ __('Dokumen Local Order') }}" class="h-[720px] w-full rounded-lg border-0"></iframe>
                    @endif
                </div>
            </section>
        </div>
    </x-common.document-workspace>
@endsection
