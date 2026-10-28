@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="font-outfit" x-data="{ filters: { search: '', date: '' }, matches(row) { const search = this.filters.search.toLowerCase(); return (!search || row.number.toLowerCase().includes(search) || row.quotation.toLowerCase().includes(search) || row.supplier.toLowerCase().includes(search)) && (!this.filters.date || row.date === this.filters.date); }, resetFilters() { this.filters = { search: '', date: '' }; } }">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
            <div class="mb-4 px-6">
                <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                    <input x-model="filters.search" type="search" placeholder="{{ __('Cari no. baucar, sebut harga atau pembekal') }}" aria-label="{{ __('Cari no. baucar, sebut harga atau pembekal') }}" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                    <input x-model="filters.date" type="date" aria-label="{{ __('Tarikh Baucar') }}" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                    <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">{{ __('See all') }}</button>
                    <a href="{{ route('baucar-bayaran.supplier.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">+ {{ __('Tambah Baucar') }}</a>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto px-6 pb-6">
                <table class="w-full min-w-[980px] text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            @foreach (['No. Baucar', 'No. Sebut Harga Pembekal', 'Pembekal', 'Tarikh', 'Cara Bayaran'] as $heading)
                                <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 dark:text-gray-400">{{ __($heading) }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-end font-semibold text-gray-500 sm:px-6 dark:text-gray-400">{{ __('Jumlah (RM)') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-500 sm:px-6 dark:text-gray-400">{{ __('Tindakan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($vouchers as $voucher)
                            <tr x-show="matches({ number: @js(strtolower($voucher->voucher_no)), quotation: @js(strtolower($voucher->quotation_no_supplier ?? '')), supplier: @js(strtolower($voucher->supplier_name ?? '')), date: @js($voucher->voucher_date) })" class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-gray-900 sm:px-6 dark:text-white/90"><a href="{{ route('baucar-bayaran.show', $voucher->id) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $voucher->voucher_no }}</a></td>
                                <td class="px-4 py-3.5 sm:px-6"><a href="{{ $voucher->supplier_quotation_id ? route('sebut-harga-pembekal.show', $voucher->supplier_quotation_id) : '#' }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $voucher->quotation_no_supplier ?: '—' }}</a><span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ $voucher->quotation_title ?: '—' }}</span></td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $voucher->supplier_name ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $voucher->payment_method ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold tabular-nums sm:px-6">RM {{ number_format((float) $voucher->total_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-center sm:px-6"><a href="{{ route('baucar-bayaran.show', $voucher->id) }}" class="document-action">{{ __('Lihat') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada baucar kepada pembekal untuk dipaparkan.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
