@props(['number', 'backUrl', 'printUrl', 'html'])
<x-common.page-breadcrumb :pageTitle="$number">
    <x-slot:actions>
        <a href="{{ $backUrl }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
    </x-slot:actions>
</x-common.page-breadcrumb>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Cetak PDF') }}</a>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
        <iframe title="{{ __('Pratonton PDF') }} {{ $number }}" sandbox class="mx-auto block h-[1123px] w-[794px] border-0 bg-white shadow-theme-sm dark:bg-white" srcdoc="{{ $html }}"></iframe>
    </div>
</div>
