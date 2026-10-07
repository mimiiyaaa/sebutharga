@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Sebut Harga Final')" />
<x-common.document-card :title="__('Senarai Sebut Harga Final')" x-data="{ filters: { search: '', date: '', status: '' }, matches(row) { const search = this.filters.search.toLowerCase(); return (!search || row.customer.toLowerCase().includes(search) || row.title.toLowerCase().includes(search)) && (!this.filters.date || row.date === this.filters.date) && (!this.filters.status || row.status === this.filters.status); }, resetFilters() { this.filters = { search: '', date: '', status: '' }; } }">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <div class="mb-4 px-6">
            <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                <input x-model="filters.search" type="search" placeholder="Cari pelanggan atau tajuk" aria-label="Cari pelanggan atau tajuk" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <input x-model="filters.date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <select x-model="filters.status" aria-label="Status" class="h-11 w-52 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300"><option value="">{{ __('Semua status') }}</option><option value="Menunggu LO">{{ __('Menunggu LO') }}</option><option value="Berjaya — Belum Ada LO">{{ __('Berjaya — Belum Ada LO') }}</option><option value="Berjaya — LO Diterima">{{ __('Berjaya — LO Diterima') }}</option></select>
                <button type="button" @click="resetFilters()" class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">See all</button>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto">
        <table class="w-full text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900"><tr>
                @foreach (['No. Sebut Harga', 'Pelanggan', 'Versi', 'Tajuk', 'Tarikh', 'Jumlah (RM)', 'Status', 'Tindakan'] as $label)
                    <th class="px-6 py-3 {{ $label === 'Tindakan' ? 'text-center' : 'text-start' }} text-theme-sm font-semibold text-gray-500 dark:text-gray-400">{{ __($label) }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse ($finals as $draft)
                    @php $document = App\Helpers\QuotationDocument::data($draft); @endphp
                    <tr x-data="{ selectedVersion: @js((string) $draft->quotation_detail_id), versions: @js($draft->versions), selectedDecision() { return this.versions.find(version => String(version.id) === String(this.selectedVersion))?.decision || 'Belum Hantar'; }, decisionClass() { return { 'Berjaya — Belum Ada LO': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400', 'Berjaya — LO Diterima': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400', 'Tidak Berjaya': 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400', 'Menunggu LO': 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400' }[this.selectedDecision()] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'; } }" x-show="matches({ customer: @js($document['company_name'] ?? $draft->company_name), date: @js($draft->quotation_date), title: @js($draft->quotation_title ?? ''), status: selectedDecision() })" class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                        <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_no }}</td>
                        <td class="px-4 py-3.5 text-gray-800 sm:px-6 dark:text-white/90">{{ $document['company_name'] ?? $draft->company_name }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 sm:px-6"><select x-model="selectedVersion" @click.stop class="h-10 min-w-30 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">@foreach ($draft->versions as $version)<option value="{{ $version['id'] }}">{{ $version['label'] }}</option>@endforeach</select></td>
                        <td class="px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_title }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-gray-700 sm:px-6 dark:text-gray-400">{{ $draft->quotation_date }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-gray-700 sm:px-6 dark:text-gray-400">{{ number_format($draft->jumlah_total, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 sm:px-6"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="decisionClass()" x-text="selectedDecision()"></span></td>
                        <td class="px-4 py-3.5 text-center sm:px-6"><div class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/60">
                            <a :href="versions.find(version => String(version.id) === String(selectedVersion))?.viewUrl" class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-medium text-gray-600 transition hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">{{ __('Lihat') }}</a>
                            <a href="{{ route('sebut-harga.preview', [$draft->quotation_id, 'draft' => $draft->quotation_detail_id, 'from' => 'final']) }}" class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-medium text-brand-600 transition hover:bg-white hover:text-brand-700 dark:text-brand-400 dark:hover:bg-gray-800 dark:hover:text-brand-300">{{ __('Pratonton Dokumen') }}</a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada sebut harga dimuktamadkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-common.document-card>
@endsection
