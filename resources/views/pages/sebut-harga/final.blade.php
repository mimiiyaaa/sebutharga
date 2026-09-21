@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Sebut Harga Final')" />
<x-common.document-card :title="__('Senarai Sebut Harga Final')" x-data="{ filters: { search: '', date: '' }, matches(row) { const search = this.filters.search.toLowerCase(); return (!search || row.customer.toLowerCase().includes(search) || row.title.toLowerCase().includes(search)) && (!this.filters.date || row.date === this.filters.date); }, resetFilters() { this.filters = { search: '', date: '' }; } }">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <div class="mb-4 px-6">
            <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                <input x-model="filters.search" type="search" placeholder="Cari pelanggan atau tajuk" aria-label="Cari pelanggan atau tajuk" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <input x-model="filters.date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">See all</button>
        </div>
        <div class="max-w-full overflow-x-auto">
        <table class="w-full text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900"><tr>
                @foreach (['No. Sebut Harga', 'Pelanggan', 'Draf', 'Tajuk', 'Tarikh', 'Jumlah (RM)', 'Tindakan'] as $label)
                    <th class="px-6 py-3 text-start text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __($label) }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse ($finals as $draft)
                    @php($document = App\Helpers\QuotationDocument::data($draft))
                    <tr x-show="matches({ customer: @js($document['company_name'] ?? $draft->company_name), date: @js($draft->quotation_date), title: @js($draft->quotation_title ?? '') })" class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                        <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_no }}</td>
                        <td class="px-4 py-3.5 text-gray-800 sm:px-6 dark:text-white/90">{{ $document['company_name'] ?? $draft->company_name }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                            <select aria-label="{{ __('Versi Final') }}" onchange="window.location.href='{{ url('/sebut-harga') }}/{{ $draft->quotation_id }}/edit?draft=' + this.value + '&from=final'" class="h-10 min-w-32 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @foreach ($draft->final_versions as $version)
                                    <option value="{{ $version->quotation_detail_id }}" @selected($version->quotation_detail_id === $draft->quotation_detail_id)>{{ __('Versi') }} {{ $version->final_no ?? $version->draft_no }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_title }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_date }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-gray-700 sm:px-6 dark:text-gray-400">{{ number_format($draft->jumlah_total, 2) }}</td>
                        <td class="px-4 py-3.5 sm:px-6"><div class="flex items-center justify-end gap-2 whitespace-nowrap">
                            <a href="{{ route('sebut-harga.edit', [$draft->quotation_id, 'draft'=>$draft->quotation_detail_id, 'from'=>'final']) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a>
                            <a href="{{ route('sebut-harga.preview', [$draft->quotation_id, 'draft'=>$draft->quotation_detail_id, 'from'=>'final']) }}" class="inline-flex items-center rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('Pratonton Dokumen') }}</a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada sebut harga dimuktamadkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-common.document-card>
@endsection
