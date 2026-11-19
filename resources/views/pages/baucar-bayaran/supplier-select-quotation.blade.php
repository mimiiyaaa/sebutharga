@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.supplier') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <form method="GET" action="{{ route('baucar-bayaran.supplier.form') }}" class="space-y-6" x-data="{ selected: '' }">
            <x-common.document-card :title="__('Pilih Sebut Harga Pembekal')" :desc="__('Pilih sebut harga pembekal yang akan dipautkan kepada baucar bayaran.')">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-start text-sm text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400"><tr><th class="w-12 px-4 py-3"></th><th class="px-4 py-3">{{ __('No. Sebut Harga Pembekal') }}</th><th class="px-4 py-3">{{ __('Pembekal') }}</th><th class="px-4 py-3">{{ __('Tajuk') }}</th><th class="px-4 py-3">{{ __('Tarikh') }}</th><th class="px-4 py-3 text-end">{{ __('Jumlah (RM)') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($quotations as $quotation)
                                <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40"><td class="px-4 py-3"><input type="radio" name="supplier_quotation_id" value="{{ $quotation->supplier_quotation_id }}" x-model="selected" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500"></td><td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $quotation->quotation_no_supplier }}</td><td class="px-4 py-3">{{ $quotation->supplier_name ?: '—' }}</td><td class="px-4 py-3">{{ $quotation->quotation_title ?: '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $quotation->quotation_date ? \Carbon\Carbon::parse($quotation->quotation_date)->format('d/m/Y') : '—' }}</td><td class="whitespace-nowrap px-4 py-3 text-end font-medium tabular-nums">RM {{ number_format((float) $quotation->total_amount, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada sebut harga pembekal tersedia.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end"><button type="submit" :disabled="!selected" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Teruskan ke Borang Baucar') }}</button></div>
            </x-common.document-card>
        </form>
    </x-common.document-workspace>
@endsection
