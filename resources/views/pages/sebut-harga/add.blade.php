@extends('layouts.app')

@section('content')
    @php
        $sourceCustomers = $sources->pluck('customerName')->filter()->unique()->sort()->values();
    @endphp

    <x-common.page-breadcrumb pageTitle="Tambah Sebut Harga" />

    <div class="space-y-6" x-data="{
        sourceType: 'Draf',
        sources: {{ Illuminate\Support\Js::from($sources) }},
        sourceCustomers: {{ Illuminate\Support\Js::from($sourceCustomers) }},
        sourceFilters: { search: '', customer: '', date: '' },
        get filteredSources() {
            const search = this.sourceFilters.search.trim().toLowerCase();
            return this.sources.filter(item => {
                const searchable = [item.quotationNo, item.customerName, item.title].join(' ').toLowerCase();
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
                <input x-model="sourceFilters.search" type="search" placeholder="{{ __('Cari no. sebut harga, syarikat atau tajuk') }}" aria-label="{{ __('Cari no. sebut harga, syarikat atau tajuk') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <select x-model="sourceFilters.customer" aria-label="{{ __('Tapis syarikat') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">{{ __('Semua syarikat') }}</option>
                    <template x-for="customer in sourceCustomers" :key="customer"><option :value="customer" x-text="customer"></option></template>
                </select>
                <input x-model="sourceFilters.date" type="date" aria-label="{{ __('Tapis tarikh') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <button type="button" @click="resetSourceFilters()" class="h-11 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('Reset') }}</button>
            </div>

            <div class="mt-5 space-y-3">
                <template x-for="group in filteredGroups" :key="group.quotationNo + group.customerName">
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-3 bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="group.quotationNo + ' — ' + group.customerName"></p><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="group.items.length + (group.items.length === 1 ? ' rekod' : ' rekod')"></p></div>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="source in group.items" :key="source.id">
                                <button type="button" @click="window.location.assign(source.editUrl)" class="flex w-full min-w-0 items-center justify-between gap-4 px-4 py-3 text-start transition hover:bg-brand-50/50 dark:hover:bg-brand-500/10">
                                    <div class="min-w-0"><p class="truncate text-sm font-medium text-gray-800 dark:text-white/90" x-text="source.title"></p><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="source.date"></p></div>
                                    <span class="inline-flex shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="source.status + ' ' + source.draftNo"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="filteredGroups.length === 0" class="rounded-xl border border-dashed border-gray-300 px-4 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('Tiada sebut harga sepadan dengan filter.') }}</div>
            </div>
        </x-common.component-card>
    </div>
@endsection
