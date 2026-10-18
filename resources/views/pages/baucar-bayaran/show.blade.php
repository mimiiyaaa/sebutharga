@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Lihat Baucar Bayaran')" :reference="$document->voucher_no">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a><a href="{{ route('baucar-bayaran.file', [$document->id, 'download' => 1]) }}" class="inline-flex h-9 items-center justify-center rounded-lg bg-brand-500 px-3 py-1.5 text-theme-xs font-medium text-white transition hover:bg-brand-600">{{ __('Muat Turun') }}</a></x-slot:referenceActions>
        <x-common.document-card :title="__('Maklumat Baucar')">
            <dl class="grid grid-cols-1 gap-5 text-sm md:grid-cols-2">
                @foreach ([__('No. Baucar') => $document->voucher_no, __('Tarikh') => \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y'), __('Dibayar Kepada') => $document->payee_name, __('Cara Bayaran') => $document->payment_method, __('Jumlah Dibayar (RM)') => number_format($document->total_amount, 2), __('Keterangan') => $document->description] as $label => $value)
                    <div class="{{ $label === __('Keterangan') ? 'md:col-span-2' : '' }}"><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium text-gray-800 dark:text-gray-200">{{ $value ?: '—' }}</dd></div>
                @endforeach
            </dl>
        </x-common.document-card>
        <x-common.document-card :title="__('Fail Baucar Bayaran')">
            <p class="mb-4 break-all text-sm text-gray-700 dark:text-gray-300">{{ $document->file_name }}</p>
            @if ($document->file_mime === 'application/pdf')
                <iframe src="{{ route('baucar-bayaran.file', $document->id) }}" title="{{ __('Baucar Bayaran') }}" class="h-[720px] w-full rounded-xl border border-gray-200 dark:border-gray-700"></iframe>
            @else
                <img src="{{ route('baucar-bayaran.file', $document->id) }}" alt="{{ __('Baucar Bayaran') }}" class="max-h-[720px] w-full rounded-xl border border-gray-200 object-contain dark:border-gray-700">
            @endif
        </x-common.document-card>
    </x-common.document-workspace>
@endsection
