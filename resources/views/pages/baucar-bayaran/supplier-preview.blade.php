@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('baucar-bayaran.show', $document->id) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali ke Maklumat Baucar') }}</a>
    <a href="{{ route('baucar-bayaran.supplier.pdf', $document->id) }}" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500">{{ __('Muat Turun PDF') }}</a>
</div>
<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
    @if($previewHtml)
        <iframe title="{{ $document->voucher_no }}" sandbox class="mx-auto block h-[1123px] w-[794px] border-0 bg-white shadow-theme-sm dark:bg-white" srcdoc="{{ $previewHtml }}"></iframe>
    @elseif(str_starts_with($document->file_mime, 'image/'))
        <img src="{{ route('baucar-bayaran.file', $document->id) }}" alt="{{ $document->voucher_no }}" class="mx-auto block h-auto max-w-full bg-white shadow-theme-sm dark:bg-white">
    @else
        <iframe title="{{ $document->voucher_no }}" src="{{ route('baucar-bayaran.supplier.pdf', $document->id) }}#toolbar=0" class="mx-auto block h-[1123px] w-[794px] border-0 bg-white shadow-theme-sm dark:bg-white"></iframe>
    @endif
</div>
@endsection
