@props(['documents' => collect()])

<div class="font-outfit" x-data="{
    tableRowData: @js($documents),
    filters: { search: '', date: '' },
    get filteredRows() {
        return this.tableRowData.filter(row => {
            const search = this.filters.search.toLowerCase();
            const customer = `${row.company_name || ''} ${row.customer_name || ''}`.toLowerCase();
            return (!search || String(row.lo_inden_no || '').toLowerCase().includes(search) || customer.includes(search))
                && (!this.filters.date || row.lo_inden_date === this.filters.date);
        });
    },
    resetFilters() {
        this.filters = { search: '', date: '' };
    },
    formatDate(value) {
        return value ? String(value).split('-').reverse().join('/') : '—';
    }
}">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <div class="mb-4 px-6">
            <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                <input id="lo_filter_search" x-model="filters.search" type="search" placeholder="Cari nombor LO atau pelanggan" aria-label="Cari nombor LO atau pelanggan" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <input id="lo_filter_date" x-model="filters.date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">See all</button>
                <a href="{{ route('lo.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">{{ __('+ Tambah Local Order') }}</a>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">No. LO</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Pelanggan / Syarikat</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Tarikh LO</th>
                        <th class="px-4 py-3 text-end font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Jumlah (RM)</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Dokumen</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in filteredRows" :key="row.lo_inden_id">
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 sm:px-6"><span class="block whitespace-nowrap font-medium text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.lo_inden_no"></span></td>
                            <td class="px-4 py-3.5 sm:px-6"><span class="block min-w-60 font-medium text-theme-sm text-gray-800 dark:text-white/90" x-text="row.company_name || row.customer_name || '—'"></span></td>
                            <td class="px-4 py-3.5 sm:px-6"><span class="whitespace-nowrap text-gray-700 text-theme-sm dark:text-gray-400" x-text="formatDate(row.lo_inden_date)"></span></td>
                            <td class="px-4 py-3.5 text-end tabular-nums sm:px-6"><span class="whitespace-nowrap font-medium text-gray-800 dark:text-white/90" x-text="Number(row.amount || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span></td>
                            <td class="px-4 py-3.5 sm:px-6">
                                <template x-if="row.document_file"><a :href="'{{ url('/lo') }}/' + row.lo_inden_id + '/dokumen'" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat Dokumen') }}</a></template>
                                <template x-if="!row.document_file"><span class="text-gray-400 dark:text-gray-500">—</span></template>
                            </td>
                            <td class="px-4 py-3.5 text-center sm:px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a :href="'{{ url('/lo') }}/' + row.lo_inden_id" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a>
                                    <a :href="'{{ url('/lo') }}/' + row.lo_inden_id + '/edit'" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a>
                                    <form :action="'{{ url('/lo') }}/' + row.lo_inden_id" method="POST" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam Local Order ini?' })">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRows.length === 0"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Tiada Local Order sepadan dengan filter.</td></tr></template>
                </tbody>
            </table>
        </div>
    </div>
</div>
