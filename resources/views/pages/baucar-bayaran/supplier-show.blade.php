@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Butiran Baucar Bayaran')" :reference="$document->voucher_no">
        <x-slot:referenceActions>
            <a href="{{ route('baucar-bayaran.supplier') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <div class="flex flex-col gap-6">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 px-6 py-6 dark:border-gray-800">
                    <x-common.document-heading :label="__('Maklumat Baucar Bayaran')" :number="$document->voucher_no" :subtitle="$supplierQuotation->supplier_name ?: __('Tiada pembekal')" />
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('baucar-bayaran.file', $document->id) }}" target="_blank" rel="noreferrer" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-gray-50 font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Lihat PDF') }}</a>
                        <a href="{{ route('baucar-bayaran.file', ['id' => $document->id, 'download' => 1]) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg bg-brand-500 font-medium text-white transition hover:bg-brand-600">{{ __('Muat Turun PDF') }}</a>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([__('No. Baucar') => $document->voucher_no, __('Tarikh') => \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y'), __('Pembekal') => $supplierQuotation->supplier_name, __('No. Sebut Harga') => $supplierQuotation->quotation_no_supplier, __('Cara Bayaran') => $document->payment_method, __('Jumlah Dibayar (RM)') => 'RM '.number_format((float) $document->total_amount, 2)] as $label => $value)
                        <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>
                    @endforeach
                    @if ($document->description)<div class="sm:col-span-2 lg:col-span-3"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Keterangan') }}</p><p class="mt-2 whitespace-pre-line break-words text-base font-medium text-gray-800 dark:text-white/90">{{ $document->description }}</p></div>@endif
                </div>
            </section>

            <div class="grid grid-cols-1 gap-5 rounded-2xl border border-gray-200 p-6 lg:grid-cols-2 dark:border-gray-800">
                @foreach ([__('Maklumat Syarikat') => ['Nama Syarikat' => config('purchase_order.issuer_name'), 'Nombor Telefon' => config('purchase_order.issuer_phone'), 'E-mel' => config('purchase_order.issuer_email'), 'Alamat' => config('purchase_order.issuer_address')], __('Maklumat Pembekal') => ['Nama Syarikat' => $supplierQuotation->supplier_name, 'Nombor Telefon' => $supplierQuotation->supplier_phone, 'E-mel' => $supplierQuotation->supplier_email, 'Alamat' => $supplierQuotation->supplier_address]] as $section => $fields)
                    <section><h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">{{ $section }}</h3><div class="grid grid-cols-1 gap-5 sm:grid-cols-2">@foreach ($fields as $label => $value)<div @class(['sm:col-span-2' => $label === 'Alamat'])><p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 whitespace-pre-line break-words text-base font-medium text-gray-800 dark:text-white/90">{{ $value ?: '—' }}</p></div>@endforeach</div></section>
                @endforeach
            </div>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Butiran Pembayaran') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Item daripada sebut harga pembekal yang dipautkan') }}</p></div>
                <div class="custom-scrollbar overflow-x-auto px-4 pb-4"><table class="w-full min-w-[760px] overflow-hidden rounded-xl text-start text-theme-sm"><thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white"><tr><th class="w-14 px-5 py-4 text-center text-theme-xs font-semibold">{{ __('Bil') }}</th><th class="w-2/5 px-5 py-4 text-start text-theme-xs font-semibold">{{ __('Perihal Barangan') }}</th><th class="px-5 py-4 text-center text-theme-xs font-semibold">{{ __('Kuantiti') }}</th><th class="px-5 py-4 text-center text-theme-xs font-semibold">{{ __('Unit') }}</th><th class="whitespace-nowrap px-5 py-4 text-end text-theme-xs font-semibold">{{ __('Harga Seunit (RM)') }}</th><th class="whitespace-nowrap px-5 py-4 text-end text-theme-xs font-semibold">{{ __('Jumlah (RM)') }}</th></tr></thead><tbody>@forelse ($supplierQuotationItems as $item)<tr class="border-b border-gray-100 even:bg-gray-50/60 hover:bg-brand-50/40 dark:border-gray-800 dark:even:bg-gray-800/30 dark:hover:bg-brand-500/5"><td class="px-5 py-5 text-center tabular-nums text-gray-400 dark:text-gray-500">{{ $loop->iteration }}</td><td class="px-5 py-5 font-medium leading-6 text-gray-800 dark:text-gray-200"><p class="whitespace-pre-line break-words">{{ $item->item_description }}</p></td><td class="whitespace-nowrap px-5 py-5 text-center tabular-nums text-gray-600 dark:text-gray-300">{{ number_format((float) $item->quantity, 2) }}</td><td class="whitespace-nowrap px-5 py-5 text-center text-gray-600 dark:text-gray-300">{{ $item->unit }}</td><td class="whitespace-nowrap px-5 py-5 text-end tabular-nums text-gray-600 dark:text-gray-300">RM {{ number_format((float) $item->unit_price, 2) }}</td><td class="whitespace-nowrap px-5 py-5 text-end font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format((float) $item->subtotal, 2) }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada item') }}</td></tr>@endforelse</tbody></table></div>
                <div class="flex justify-end border-t border-gray-100 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-900"><div class="w-full max-w-sm text-theme-sm text-gray-700 dark:text-gray-300"><div class="flex justify-between border-t border-gray-200 pt-3 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white/90"><span>{{ __('Jumlah Dibayar') }}</span><span class="tabular-nums">RM {{ number_format((float) $document->total_amount, 2) }}</span></div></div></div>
            </section>
        </div>
    </x-common.document-workspace>
@endsection
