@props(['orders' => collect()])

<div class="font-outfit" x-data="{
    tableRowData: @js($orders),
    filterOpen: false,
    statusOpen: false,
    filters: { search: '', status: '', date: '' },
    get filteredRows() {
        return this.tableRowData.filter(row => {
            const search = this.filters.search.toLowerCase();
            const haystack = `${row.po_no || ''} ${row.company_name || ''} ${row.quotation_reference_no || ''}`.toLowerCase();
            return (!this.filters.status || String(row.status_po || '') === this.filters.status)
                && (!search || haystack.includes(search))
                && (!this.filters.date || row.po_date === this.filters.date);
        });
    },
    resetFilters() {
        this.filters = { search: '', status: '', date: '' };
        this.filterOpen = false;
    },
    formatDate(value) {
        return value ? String(value).split('-').reverse().join('/') : '—';
    },
    getStatusClass(status) {
        if (status === 'Draf') return 'bg-peach-200 text-peach-700 dark:bg-peach-500/25 dark:text-peach-300';
        return 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400';
    }
}">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <div class="mb-4 px-6">
            <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                <input id="po_filter_search" x-model="filters.search" type="search" placeholder="Cari nombor PO, pembekal atau sebut harga" aria-label="Cari nombor PO, pembekal atau sebut harga" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <div class="relative w-40 shrink-0" @click.outside="statusOpen = false">
                    <button id="po_filter_status" type="button" @click="statusOpen = !statusOpen" :aria-expanded="statusOpen" aria-haspopup="listbox" aria-label="Status" class="flex h-11 w-full items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 text-start text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:border-brand-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-700">
                        <span x-text="filters.status || 'Semua status'"></span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition" :class="statusOpen ? 'rotate-180 text-brand-500' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="statusOpen" x-cloak x-transition.origin.top class="absolute start-0 top-full z-999 mt-2 w-full min-w-36 overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-700 dark:bg-gray-800" role="listbox" aria-label="Pilihan status">
                        <button type="button" @click="filters.status = ''; statusOpen = false" :class="filters.status === '' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Semua status<span x-show="filters.status === ''" class="text-brand-500">✓</span></button>
                        <button type="button" @click="filters.status = 'Draf'; statusOpen = false" :class="filters.status === 'Draf' ? 'bg-peach-100 text-peach-700 dark:bg-peach-500/15 dark:text-peach-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Draf<span x-show="filters.status === 'Draf'" class="text-peach-500">✓</span></button>
                        <button type="button" @click="filters.status = 'Diluluskan'; statusOpen = false" :class="filters.status === 'Diluluskan' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Diluluskan<span x-show="filters.status === 'Diluluskan'" class="text-success-500">✓</span></button>
                    </div>
                </div>
                <input id="po_filter_date" x-model="filters.date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">See all</button>
                <a href="{{ route('purchase-order.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">{{ __('+ Tambah Purchase Order') }}</a>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
                <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">No. Purchase Order</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Sebut Harga</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Pembekal</th>
                        <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Tarikh</th>
                        <th class="px-4 py-3 text-end font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Jumlah (RM)</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in filteredRows" :key="row.purchase_order_id">
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-white/[0.05] dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 sm:px-6"><span class="block whitespace-nowrap font-medium text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.po_no"></span></td>
                            <td class="px-4 py-3.5 sm:px-6"><span class="block whitespace-nowrap text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.quotation_reference_no || row.quotation_detail_id || '—'"></span></td>
                            <td class="px-4 py-3.5 sm:px-6"><span class="block min-w-52 font-medium text-theme-sm text-gray-800 dark:text-white/90" x-text="row.company_name || '—'"></span></td>
                            <td class="px-4 py-3.5 sm:px-6"><p class="whitespace-nowrap text-gray-700 text-theme-sm dark:text-gray-400" x-text="formatDate(row.po_date)"></p></td>
                            <td class="px-4 py-3.5 text-end tabular-nums sm:px-6"><span class="whitespace-nowrap font-medium text-gray-800 dark:text-white/90" x-text="Number(row.net_amount || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span></td>
                            <td class="px-4 py-3.5 text-center sm:px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a :href="'{{ url('/purchase-order') }}/' + row.purchase_order_id" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a>
                                    <a :href="'{{ url('/purchase-order') }}/' + row.purchase_order_id + '/edit'" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a>
                                    <form :action="'{{ url('/purchase-order') }}/' + row.purchase_order_id" method="POST" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam Purchase Order ini?' })">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRows.length === 0"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Tiada Purchase Order sepadan dengan filter.</td></tr></template>
                </tbody>
            </table>
        </div>
    </div>
</div>
