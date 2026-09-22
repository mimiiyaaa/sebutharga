@props(['quotations' => collect()])

<div class="font-outfit" x-data="{
    tableRowData: @js($quotations),
    filterOpen: false,
    statusOpen: false,
    filters: { search: '', status: '', date: '' },
    get filteredRows() {
        return this.tableRowData.filter(row => {
            const search = this.filters.search.toLowerCase();
            const customer = (row.customerName || '').toLowerCase();
            const title = (row.product || '').toLowerCase();
            return (!this.filters.status || row.status === this.filters.status)
                && (!search || customer.includes(search) || title.includes(search))
                && (!this.filters.date || row.closeDate === this.filters.date)
                ;
        });
    },
    resetFilters() {
        this.filters = { search: '', status: '', date: '' };
        this.filterOpen = false;
    },
    selectedDraft(row) {
        return row.versions.find(draft => String(draft.id) === String(row.version)) || {};
    },
    getStatusClass(status) {
        const classes = {
            'Sudah Difinalisekan': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            'Belum Difinalisekan': 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        };
        return classes[status] || '';
    }
}">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="mb-4 px-6">
            <div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
                <input id="draft_filter_search" x-model="filters.search" type="search" placeholder="Cari pelanggan atau tajuk" aria-label="Cari pelanggan atau tajuk" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <div class="relative w-40 shrink-0" @click.outside="statusOpen = false">
                    <button id="draft_filter_status" type="button" @click="statusOpen = !statusOpen" :aria-expanded="statusOpen" aria-haspopup="listbox" aria-label="Status" class="flex h-11 w-full items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 text-start text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:border-brand-300 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-700">
                        <span x-text="filters.status || 'Semua status'"></span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition" :class="statusOpen ? 'rotate-180 text-brand-500' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="statusOpen" x-cloak x-transition.origin.top class="absolute start-0 top-full z-999 mt-2 w-full min-w-36 overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-700 dark:bg-gray-800" role="listbox" aria-label="Pilihan status">
                        <button type="button" @click="filters.status = ''; statusOpen = false" :class="filters.status === '' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Semua status<span x-show="filters.status === ''" class="text-brand-500">✓</span></button>
                        <button type="button" @click="filters.status = 'Belum Difinalisekan'; statusOpen = false" :class="filters.status === 'Belum Difinalisekan' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Belum Difinalisekan<span x-show="filters.status === 'Belum Difinalisekan'" class="text-warning-500">✓</span></button>
                        <button type="button" @click="filters.status = 'Sudah Difinalisekan'; statusOpen = false" :class="filters.status === 'Sudah Difinalisekan' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-theme-sm font-medium transition">Sudah Difinalisekan<span x-show="filters.status === 'Sudah Difinalisekan'" class="text-success-500">✓</span></button>
                    </div>
                </div>
                <input id="draft_filter_date" x-model="filters.date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300">
                <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    See all
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
                <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">No. Sebut Harga</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">Pelanggan</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">Tajuk</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">{{ __('Draf') }}</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">Status</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">Tarikh</th>
                        <th class="px-6 py-3 font-semibold text-gray-500 sm:px-6 text-theme-sm dark:text-gray-400 text-start">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in filteredRows" :key="row.id">
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-4 sm:px-6 py-3.5">
                                <div>
                                    <div>
                                        <span class="block whitespace-nowrap font-medium text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.quotationNo"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center shrink-0 w-9 h-9 rounded-full font-medium text-sm"
                                        :class="[row.avatarBg, row.avatarColor]">
                                        <span x-text="row.initials"></span>
                                    </div>
                                    <div>
                                        <span class="block font-medium text-theme-sm text-gray-800 dark:text-white/90" x-text="row.customerName"></span>
                                        <span class="text-gray-500 text-theme-xs dark:text-gray-400" x-text="row.customerEmail"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.product"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <select x-model="row.version" x-effect="$nextTick(() => { $el.value = String(row.version); })" aria-label="{{ __('Draf') }}" class="h-10 min-w-40 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    @change="const version = row.versions.find(item => String(item.id) === String($event.target.value)); if (version) { row.version = version.id; row.product = version.title; row.closeDate = version.date; row.status = version.status; row.decision = version.decision; }">
                                    <template x-for="version in row.versions" :key="version.id">
                                        <option :value="version.id" x-text="version.name" ></option>
                                    </template>
                                </select>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <span class="text-theme-xs inline-block rounded-full px-2 py-0.5 font-medium"
                                    :class="getStatusClass(selectedDraft(row).decision)"
                                    x-text="selectedDraft(row).decision"></span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="whitespace-nowrap text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.closeDate"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-2">
                                    <a :href="'{{ url('/sebut-harga') }}/' + row.id + '/edit?draft=' + row.version" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a>
                                    <form :action="'{{ url('/sebut-harga') }}/' + row.id + '/draft/' + row.version" method="POST" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam draf yang dipilih sahaja?' })">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRows.length === 0">
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Tiada sebut harga sepadan dengan filter.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
