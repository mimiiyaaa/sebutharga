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

            <div class="max-w-full overflow-x-auto custom-scrollbar px-6 pb-6">
                <table class="w-full min-w-[980px] text-theme-sm font-medium leading-6 text-gray-700 dark:text-gray-300">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            @foreach (['No. Baucar', 'No. Sebut Harga Pembekal', 'Pembekal', 'Tarikh', 'Cara Bayaran', 'No. Cek'] as $heading)
                                <th class="px-6 py-3 text-start text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>
                            @endforeach
                            <th class="px-6 py-3 text-end text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('Jumlah (RM)') }}</th>
                            <th class="px-6 py-3 text-center text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('Tindakan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($vouchers as $voucher)
                            <tr x-show="matches({ number: @js(strtolower($voucher->voucher_no)), quotation: @js(strtolower($voucher->quotation_no_supplier ?? '')), supplier: @js(strtolower($voucher->supplier_name ?? '')), date: @js($voucher->voucher_date) })" class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                                <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400"><a href="{{ route('baucar-bayaran.show', $voucher->id) }}" class="font-semibold text-gray-800 hover:underline dark:text-white/90">{{ $voucher->voucher_no }}</a><span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</span></td>
                                <td class="px-4 py-3.5 sm:px-6"><span class="font-medium text-gray-700 dark:text-gray-300">{{ $voucher->quotation_no_supplier ?: '—' }}</span><span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ $voucher->quotation_title ?: '—' }}</span></td>
                                <td class="px-4 py-3.5 text-gray-800 sm:px-6 dark:text-white/90">{{ $voucher->supplier_name ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $voucher->payment_method ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $voucher->cheque_no ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-end tabular-nums text-gray-700 sm:px-6 dark:text-gray-400">RM {{ number_format((float) $voucher->total_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-center sm:px-6"><div class="flex items-center justify-center gap-2"><a href="{{ route('baucar-bayaran.show', $voucher->id) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a><a href="{{ route('baucar-bayaran.supplier.edit', $voucher->id) }}" class="document-action document-action-primary">{{ __('Edit') }}</a><form method="POST" action="{{ route('baucar-bayaran.delete', $voucher->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini?')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l-1-13M10 11v5m4-5v5"/></svg></button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada baucar kepada pembekal untuk dipaparkan.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
