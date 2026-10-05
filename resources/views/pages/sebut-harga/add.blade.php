@extends('layouts.app')

@section('content')
    @php
        $sourceCustomers = $sources->pluck('customerName')->filter()->unique()->sort()->values();
    @endphp

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah Sebut Harga') }}</h2>
    </div>

    <div class="space-y-6" x-data="{
        sourceType: 'Draf',
        sources: {{ Illuminate\Support\Js::from($sources) }},
        sourceCustomers: {{ Illuminate\Support\Js::from($sourceCustomers) }},
        sourceFilters: { search: '', customer: '', date: '' },
        get filteredSources() {
            const search = this.sourceFilters.search.trim().toLowerCase();
            return this.sources.filter(item => {
            const searchable = [item.quotationNo, item.customerName, item.title, ...(item.items || [])].join(' ').toLowerCase();
                return item.status === this.sourceType
                    && (!search || searchable.includes(search))
                    && (!this.sourceFilters.customer || item.customerName === this.sourceFilters.customer)
                    && (!this.sourceFilters.date || item.date === this.sourceFilters.date);
            });
        },
        get filteredGroups() {
            return Object.values(this.filteredSources.reduce((groups, item) => {
                const key = item.quotationNo + '|' + item.customerName;
                if (!groups[key]) {
                    groups[key] = { quotationNo: item.quotationNo, customerName: item.customerName, items: [] };
                }
                groups[key].items.push(item);
                return groups;
            }, {}));
        },
        resetSourceFilters() {
            this.sourceFilters = { search: '', customer: '', date: '' };
        }
    }">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih borang kosong atau sebut harga sedia ada.') }}</p>
            </div>
            <a href="{{ route('sebut-harga.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Buat Sebut Harga Baru') }}</a>
        </div>

        <x-common.component-card title="Sebut Harga Sedia Ada">
            <x-slot:header>
                <div class="inline-flex rounded-lg border border-gray-200 p-1 dark:border-gray-700">
                    <button type="button" @click="sourceType = 'Draf'" :class="sourceType === 'Draf' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300'" class="rounded-md px-3 py-1.5 text-xs font-medium transition">{{ __('Draf') }}</button>
                    <button type="button" @click="sourceType = 'Final'" :class="sourceType === 'Final' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300'" class="rounded-md px-3 py-1.5 text-xs font-medium transition">{{ __('Final') }}</button>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,220px)_minmax(0,160px)_auto]">
                <input x-model="sourceFilters.search" type="search" placeholder="{{ __('Cari no. sebut harga, syarikat, tajuk atau item') }}" aria-label="{{ __('Cari no. sebut harga, syarikat, tajuk atau item') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <select x-model="sourceFilters.customer" aria-label="{{ __('Tapis syarikat') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">{{ __('Semua syarikat') }}</option>
                    <template x-for="customer in sourceCustomers" :key="customer"><option :value="customer" x-text="customer"></option></template>
                </select>
                <input x-model="sourceFilters.date" type="date" aria-label="{{ __('Tapis tarikh') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <button type="button" @click="resetSourceFilters()" class="h-11 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('Reset') }}</button>
            </div>

            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full min-w-[900px] text-start text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/60">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">No. Sebut Harga</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Pelanggan</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tajuk</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Versi</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tarikh</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="group in filteredGroups" :key="group.quotationNo + '|' + group.customerName">
                                <tr x-data="{ selectedId: group.items[0]?.id, selected() { return group.items.find(item => String(item.id) === String(this.selectedId)) || group.items[0] } }" class="border-t border-gray-100 transition hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-500/10">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-800 dark:text-white/90" x-text="group.quotationNo"></td>
                                    <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300" x-text="group.customerName"></td>
                                    <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300" x-text="selected()?.title"></td>
                                    <td class="px-4 py-3.5"><select x-model="selectedId" class="h-9 min-w-28 rounded-lg border border-gray-200 bg-white px-2.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><template x-for="item in group.items" :key="item.id"><option :value="item.id" x-text="item.status + ' ' + item.draftNo"></option></template></select></td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-gray-600 dark:text-gray-400" x-text="selected()?.date"></td>
                                    <td class="px-4 py-3.5"><span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="selected()?.status + ' ' + selected()?.draftNo"></span></td>
                                    <td class="px-4 py-3.5 text-center"><button type="button" @click="window.location.assign(selected()?.editUrl)" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-white dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Guna Semula') }}</button></td>
                                </tr>
                            </template>
                            <tr x-show="filteredGroups.length === 0"><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Tiada sebut harga sepadan dengan filter.') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
