@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Butiran LO')" />
    @php
        $documentIsImage = in_array(strtolower(pathinfo((string) $lo->document_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    @endphp
    <div class="space-y-6">
        <div class="flex justify-end gap-3"><a href="{{ route('lo') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a><a href="{{ route('lo.edit', $lo->lo_inden_id) }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a></div>
        <x-common.document-card :title="__('Maklumat LO')"><div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No. LO') }}</p><p class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $lo->lo_inden_no }}</p></div><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pelanggan / Syarikat') }}</p><p class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $lo->company_name ?: ($lo->customer_name ?: '—') }}</p></div><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tarikh LO') }}</p><p class="mt-2 font-semibold text-gray-800 dark:text-white/90">{{ \Carbon\Carbon::parse($lo->lo_inden_date)->format('d/m/Y') }}</p></div><div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Jumlah (RM)') }}</p><p class="mt-2 font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ number_format((float) $lo->amount, 2) }}</p></div></div></x-common.document-card>
        <x-common.document-card :title="__('Dokumen LO')"><div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-950/40">@if($documentIsImage)<img src="{{ route('lo.document', $lo->lo_inden_id) }}" alt="{{ __('Dokumen LO') }}" class="mx-auto max-h-[720px] w-auto max-w-full object-contain">@else<iframe src="{{ route('lo.document', $lo->lo_inden_id) }}" title="{{ __('Dokumen LO') }}" class="h-[720px] w-full rounded-lg border-0"></iframe>@endif</div></x-common.document-card>
    </div>
@endsection
