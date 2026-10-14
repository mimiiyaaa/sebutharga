@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$quotation->quotation_no_supplier">
    <x-slot:referenceActions>
        <a href="{{ route('sebut-harga-pembekal') }}" class="document-action">{{ __('Kembali') }}</a>
        <a href="{{ route('sebut-harga-pembekal.edit', $quotation->supplier_quotation_id) }}" class="document-action document-action-primary">{{ __('Edit') }}</a>
        @if($quotation->file_path)
            <a href="{{ route('sebut-harga-pembekal.download', $quotation->supplier_quotation_id) }}" class="document-action">{{ __('Muat Turun Dokumen Asal') }}</a>
        @endif
    </x-slot:referenceActions>
    <div class="grid gap-6 xl:grid-cols-2">
        <x-common.document-card :title="__('Dokumen Pembekal')">
            @if($quotation->file_path)
                <p class="break-words text-sm text-gray-700 dark:text-gray-300">{{ $quotation->file_name }}</p>
                @if(in_array(strtolower(pathinfo($quotation->file_path, PATHINFO_EXTENSION)), ['jpg','jpeg','png']))
                    <img src="{{ asset('storage/'.$quotation->file_path) }}" alt="{{ __('Dokumen Pembekal') }}" class="max-h-[640px] w-full object-contain">
                @else
                    <iframe src="{{ asset('storage/'.$quotation->file_path) }}" title="{{ __('Dokumen Pembekal') }}" class="h-[640px] w-full rounded-xl border border-gray-200 dark:border-gray-700"></iframe>
                @endif
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tiada dokumen dimuat naik.') }}</p>
            @endif
        </x-common.document-card>
        <x-common.document-card :title="__('Maklumat Sebut Harga')">
            <dl class="space-y-5 text-sm">
                @foreach([
                    __('Nama Syarikat') => $quotation->company_name,
                    __('No. Sebut Harga Pembekal') => $quotation->quotation_no_supplier,
                    __('Tarikh Sebut Harga') => $quotation->quotation_date ? \Carbon\Carbon::parse($quotation->quotation_date)->format('d/m/Y') : null,
                    __('Tajuk Sebut Harga') => $quotation->quotation_title,
                    __('Sumber Sebut Harga') => $quotation->sumber_sebut_harga,
                    __('Person In Charge') => $quotation->person_in_charge,
                ] as $label => $value)
                    <div><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium text-gray-800 dark:text-gray-200">{{ filled($value) ? $value : '—' }}</dd></div>
                @endforeach
            </dl>
            <div class="border-t border-gray-200 pt-4 text-lg font-semibold text-gray-800 dark:border-gray-700 dark:text-gray-200">{{ __('Jumlah (RM)') }}: {{ number_format($items->sum('subtotal'), 2) }}</div>
        </x-common.document-card>
    </div>
    <x-common.document-card :title="__('Item Dokumen')">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800"><tr>
                    @foreach(['Bil','Keterangan Barang','Kuantiti','Unit','Harga Seunit (RM)','Jumlah (RM)'] as $heading)<th class="p-3 text-start">{{ __($heading) }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($items as $item)
                        <tr><td class="p-3">{{ $loop->iteration }}</td><td class="whitespace-pre-line p-3">{{ $item->item_description }}</td><td class="p-3">{{ number_format($item->quantity, 2) }}</td><td class="p-3">{{ $item->unit }}</td><td class="p-3 tabular-nums">{{ number_format($item->unit_price, 2) }}</td><td class="p-3 tabular-nums">{{ number_format($item->subtotal, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.document-card>
</x-common.document-workspace>
@endsection
