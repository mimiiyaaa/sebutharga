@props(['quotations' => collect()])

<div class="font-outfit" x-data="{
    tableRowData: @js($quotations),
    selectedDraft(row) {
        return row.versions.find(draft => String(draft.id) === String(row.version)) || {};
    },
    getStatusClass(status) {
        const classes = {
            'Final': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            'Draf': 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'Setuju': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            'Menunggu Keputusan': 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'Tidak Setuju': 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
        };
        return classes[status] || '';
    }
}">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-4 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Sebut Harga
                </h3>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sebut-harga.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white hover:bg-brand-600">
                    + Tambah Sebut Harga
                </a>
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"/>
                        <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"/>
                    </svg>
                    Filter
                </button>
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
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
                    <template x-for="row in tableRowData" :key="row.id">
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
                                    <form :action="'{{ url('/sebut-harga') }}/' + row.id + '/draft/' + row.version" method="POST" @submit="if (!confirm('Padam draf yang dipilih sahaja?')) $event.preventDefault()">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
