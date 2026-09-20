@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Sebut Harga Pembekal')" />
<x-common.component-card :title="__('Senarai Sebut Harga Pembekal')" x-data="{ filters: { company: '', title: '', person: '' }, matches(row) { return (!this.filters.company || row.company.toLowerCase().includes(this.filters.company.toLowerCase())) && (!this.filters.title || row.title.toLowerCase().includes(this.filters.title.toLowerCase())) && (!this.filters.person || row.person.toLowerCase().includes(this.filters.person.toLowerCase())); }, resetFilters() { this.filters = { company: '', title: '', person: '' }; } }">
    @if (session('success'))<p class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>@endif
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <div class="mb-4 flex flex-col gap-3 px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <input x-model="filters.company" type="search" placeholder="Cari syarikat" aria-label="Syarikat" class="h-12 w-44 rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <input x-model="filters.title" type="search" placeholder="Cari tajuk" aria-label="Tajuk" class="h-12 w-36 rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <input x-model="filters.person" type="search" placeholder="Cari PIC" aria-label="Person In Charge" class="h-12 w-36 rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="resetFilters()" class="inline-flex h-12 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">See all</button>
                <a href="{{ route('sebut-harga-pembekal.create') }}" class="inline-flex h-12 items-center gap-2 rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white hover:bg-brand-600">+ {{ __('Tambah Sebut Harga Pembekal') }}</a>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar"><table class="w-full min-w-[850px] text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900"><tr>@foreach (['No. Sebut Harga', 'Pembekal', 'Tajuk', 'Person In Charge', 'Tindakan'] as $heading)<th class="px-6 py-3 text-start text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>@endforeach</tr></thead>
            <tbody>@forelse ($quotations as $quotation)<tr x-show="matches({ company: @js($quotation->company_name ?? ''), title: @js($quotation->quotation_title ?? ''), person: @js($quotation->person_in_charge ?? '') })" class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $quotation->quotation_no_supplier }}</td><td class="px-4 py-3.5 text-gray-800 sm:px-6 dark:text-white/90">{{ $quotation->company_name }}</td><td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $quotation->quotation_title ?: '—' }}</td><td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $quotation->person_in_charge ?: '—' }}</td><td class="whitespace-nowrap px-4 py-3.5 sm:px-6"><div class="flex items-center justify-end gap-2"><a href="{{ route('sebut-harga-pembekal.edit', $quotation->supplier_quotation_id) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a><form method="POST" action="{{ route('sebut-harga-pembekal.delete', $quotation->supplier_quotation_id) }}" onsubmit="return confirm('{{ __('Padam rekod ini?') }}')">@csrf @method('DELETE')<button class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10" title="{{ __('Padam') }}"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button></form></div></td>
            </tr>@empty<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td></tr>@endforelse</tbody>
        </table></div>
    </div>
</x-common.component-card>
@endsection
