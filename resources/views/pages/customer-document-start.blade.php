@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title">
        <x-slot:referenceActions>
            <a href="{{ route($backRoute) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <x-common.document-card :title="$documentType" :desc="__('Sediakan dokumen baharu untuk pelanggan.')">
            <div class="flex flex-col items-start justify-between gap-5 rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-5 dark:border-gray-700 dark:bg-gray-800/40 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Belum ada dokumen dicipta') }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Cipta dokumen untuk memasukkan maklumat pelanggan dan item.') }}</p>
                </div>
                <a href="{{ route($createRoute) }}" class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">{{ $createButtonLabel }}</a>
            </div>
        </x-common.document-card>
    </x-common.document-workspace>
@endsection
