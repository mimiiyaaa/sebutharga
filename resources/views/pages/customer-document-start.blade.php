@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title" :subtitle="__('Pilih Local Order untuk menyediakan Delivery Order kepada pelanggan.')">
        <x-slot:referenceActions>
            <a href="{{ route($backRoute) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-white font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <div class="space-y-6" x-data="{ loId: @js((string) old('lo_inden_id', '')), selectionError: false, filters: { search: '', customer: '', date: '' }, localOrders: @js($localOrders), get customers() { return [...new Set(this.localOrders.map(row => row.company_name || row.customer_name).filter(Boolean))].sort(); }, get filteredOrders() { const search = this.filters.search.trim().toLowerCase(); return this.localOrders.filter(row => { const text = [row.lo_inden_no, row.company_name, row.customer_name, row.quotation_no, row.quotation_title].join(' ').toLowerCase(); return (!search || text.includes(search)) && (!this.filters.customer || (row.company_name || row.customer_name) === this.filters.customer) && (!this.filters.date || row.lo_inden_date === this.filters.date); }); }, selectLo(id) { this.loId = String(id); this.selectionError = false; }, resetFilters() { this.filters = { search: '', customer: '', date: '' }; } }">
            <div class="flex items-center justify-between gap-3"><div><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih Local Order untuk menyediakan dokumen kepada pelanggan.') }}</p></div></div>
            <x-common.component-card :title="__('Local Order Sedia Ada')">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,220px)_minmax(0,160px)_auto]">
                    <input x-model="filters.search" type="search" placeholder="{{ __('Cari no. LO, pelanggan, sebut harga atau tajuk') }}" aria-label="{{ __('Cari Local Order') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <select x-model="filters.customer" aria-label="{{ __('Tapis pelanggan') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><option value="">{{ __('Semua pelanggan') }}</option><template x-for="customer in customers" :key="customer"><option :value="customer" x-text="customer"></option></template></select>
                    <input x-model="filters.date" type="date" aria-label="{{ __('Tapis tarikh') }}" class="h-11 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <button type="button" @click="resetFilters()" class="h-11 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('Reset') }}</button>
                </div>
                <form method="GET" action="{{ route($createRoute) }}" class="mt-5" @submit.prevent="if (!loId) { selectionError = true; return; } $el.submit()">
                    <input type="hidden" name="lo_inden_id" x-model="loId">
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"><div class="max-w-full overflow-x-auto"><table class="w-full min-w-[950px] text-start text-sm"><thead class="bg-gray-50 dark:bg-gray-800/60"><tr><th class="w-12 px-4 py-3"></th><th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ __('No. Local Order') }}</th><th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ __('Pelanggan') }}</th><th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ __('No. Sebut Harga') }}</th><th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ __('Tajuk') }}</th><th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ __('Tarikh') }}</th></tr></thead><tbody>
                        <template x-for="row in filteredOrders" :key="row.lo_inden_id"><tr @click="selectLo(row.lo_inden_id)" :class="String(loId) === String(row.lo_inden_id) ? 'bg-brand-50 dark:bg-brand-500/10' : 'border-t border-gray-100 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-500/10'" class="cursor-pointer transition"><td class="px-4 py-3.5 text-center"><input type="radio" name="lo_choice" :value="row.lo_inden_id" :checked="String(loId) === String(row.lo_inden_id)" @click.stop="selectLo(row.lo_inden_id)" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500"></td><td class="whitespace-nowrap px-4 py-3.5 font-medium text-gray-800 dark:text-white/90" x-text="row.lo_inden_no"></td><td class="px-4 py-3.5 text-gray-700 dark:text-gray-300" x-text="row.company_name || row.customer_name || '—'"></td><td class="whitespace-nowrap px-4 py-3.5 text-gray-700 dark:text-gray-300" x-text="row.quotation_no || '—'"></td><td class="px-4 py-3.5 text-gray-700 dark:text-gray-300" x-text="row.quotation_title || '—'"></td><td class="whitespace-nowrap px-4 py-3.5 text-gray-600 dark:text-gray-400" x-text="row.lo_inden_date || '—'"></td></tr></template>
                        <tr x-show="filteredOrders.length === 0"><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Tiada Local Order sepadan dengan filter.') }}</td></tr>
                    </tbody></table></div></div>
                    <p x-cloak x-show="selectionError" class="mt-3 text-sm text-error-600 dark:text-error-400">{{ __('Sila pilih satu Local Order.') }}</p>
                    <div class="mt-4 flex flex-wrap justify-end gap-2"><button type="submit" :disabled="!loId" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ $createButtonLabel }}</button>@if($allowWithoutLocalOrder ?? false)<a href="{{ route($createRoute) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Teruskan Tanpa Local Order') }}</a>@endif</div>
                </form>
            </x-common.component-card>
        </div>

        <form method="GET" action="{{ route($createRoute) }}" class="hidden space-y-6" @submit.prevent="if (!loId) { selectionError = true; return; } $el.submit()" x-data="{ loId: @js((string) old('lo_inden_id', '')), selectionError: false, localOrders: @js($localOrders), selectLo(id) { this.loId = String(id); this.selectionError = false; } }">
            <x-common.document-card :title="__('Pilih Local Order (LO)')" :desc="__('Pilih satu Local Order untuk dijadikan rujukan dokumen.')">
                @if ($errors->any())<p role="alert" class="mb-4 rounded-xl bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Sila pilih Local Order yang sah.') }}</p>@endif
                @if ($localOrders->isEmpty())
                    <p class="rounded-xl bg-peach-100 p-4 text-sm text-peach-700 dark:bg-peach-500/15 dark:text-peach-300">{{ __('Belum ada Local Order tersedia.') }}</p>
                @else
                    <input type="hidden" name="lo_inden_id" x-model="loId">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400"><tr><th class="w-12 px-4 py-3"></th><th class="px-4 py-3 text-start">{{ __('No. Local Order (LO)') }}</th><th class="px-4 py-3 text-start">{{ __('Pelanggan') }}</th><th class="px-4 py-3 text-start">{{ __('No. Sebut Harga') }}</th><th class="px-4 py-3 text-start">{{ __('Tajuk') }}</th><th class="px-4 py-3 text-start">{{ __('Tarikh') }}</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="row in localOrders" :key="row.lo_inden_id">
                                    <tr @click="selectLo(row.lo_inden_id)" :class="String(loId) === String(row.lo_inden_id) ? 'bg-brand-50 dark:bg-brand-500/10' : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/40'" class="cursor-pointer transition">
                                        <td class="px-4 py-3 text-center"><input type="radio" name="lo_choice" :value="row.lo_inden_id" :checked="String(loId) === String(row.lo_inden_id)" @click.stop="selectLo(row.lo_inden_id)" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200" x-text="row.lo_inden_no"></td>
                                        <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.company_name || row.customer_name || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_no || '—'"></td>
                                        <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_title || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.lo_inden_date || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p x-cloak x-show="selectionError" class="mt-3 text-sm text-error-600 dark:text-error-400">{{ __('Sila pilih satu Local Order.') }}</p>
                    <div class="mt-4 flex justify-end"><button type="submit" :disabled="!loId" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ $createButtonLabel }}</button></div>
                @endif
            </x-common.document-card>
        </form>

        <x-common.document-card class="hidden" :title="__('Langkah 1: Pilih Local Order (LO)')" :desc="__('Pilih LO untuk dimasukkan ke dalam Delivery Order.')">
            @if ($errors->any())
                <p role="alert" class="rounded-xl bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Sila pilih Local Order yang sah.') }}</p>
            @endif

            @if ($localOrders->isEmpty())
                <p class="rounded-xl bg-peach-100 p-4 text-sm text-peach-700 dark:bg-peach-500/15 dark:text-peach-300">{{ __('Belum ada Local Order tersedia.') }}</p>
            @else
                <form method="GET" action="{{ route($createRoute) }}" class="space-y-5" x-data="{ loId: @js((string) old('lo_inden_id', '')), dropdownOpen: false, localOrders: @js($localOrders), get lo() { return this.localOrders.find(row => String(row.lo_inden_id) === String(this.loId)); }, selectLo(id) { this.loId = String(id); this.dropdownOpen = false; }, formatDate(value) { if (!value) return '—'; return value; } }">
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/70 px-4 py-3 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-xs font-bold text-white">1</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ __('Pilih dokumen rujukan') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Maklumat pelanggan dan sebut harga berkaitan akan dipaparkan.') }}</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-brand-700 shadow-theme-xs dark:bg-gray-900 dark:text-brand-300">{{ __('Langkah 1 daripada 2') }}</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400"><tr><th class="w-12 px-4 py-3"></th><th class="px-4 py-3 text-start">{{ __('No. Local Order (LO)') }}</th><th class="px-4 py-3 text-start">{{ __('Pelanggan') }}</th><th class="px-4 py-3 text-start">{{ __('No. Sebut Harga') }}</th><th class="px-4 py-3 text-start">{{ __('Tajuk') }}</th><th class="px-4 py-3 text-start">{{ __('Tarikh') }}</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="row in localOrders" :key="row.lo_inden_id">
                                    <tr @click="selectLo(row.lo_inden_id)" :class="String(loId) === String(row.lo_inden_id) ? 'bg-brand-50 dark:bg-brand-500/10' : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/40'" class="cursor-pointer transition">
                                        <td class="px-4 py-3 text-center"><input type="radio" name="lo_choice" :value="row.lo_inden_id" :checked="String(loId) === String(row.lo_inden_id)" @click.stop="selectLo(row.lo_inden_id)" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200" x-text="row.lo_inden_no"></td>
                                        <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.company_name || row.customer_name || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_no || '—'"></td>
                                        <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_title || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.lo_inden_date || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="hidden">
                        <label for="lo_inden_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Local Order (LO)') }} <span class="text-error-500">*</span></label>
                        <input type="hidden" name="lo_inden_id" x-model="loId">
                        <div class="relative w-full" @click.outside="dropdownOpen = false">
                            <button type="button" id="lo_inden_id" @click="dropdownOpen = !dropdownOpen" :aria-expanded="dropdownOpen" class="flex min-h-12 w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-start text-sm text-gray-800 outline-none transition hover:border-brand-300 hover:bg-white focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90 dark:hover:border-brand-500/50 dark:hover:bg-gray-800 dark:focus:bg-gray-900">
                                <span class="truncate" :class="lo ? 'font-medium text-gray-900 dark:text-white/90' : 'text-gray-500 dark:text-gray-400'" x-text="lo ? `${lo.lo_inden_no} — ${lo.company_name || lo.customer_name} — ${lo.lo_inden_date}` : '{{ __('Pilih Local Order (LO)') }}'"></span>
                                <svg class="h-4 w-4 shrink-0 text-gray-500 transition-transform dark:text-gray-400" :class="dropdownOpen ? 'rotate-180 text-brand-500' : ''" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-cloak x-show="dropdownOpen" x-transition class="mt-2 max-h-[50vh] w-full overflow-y-scroll overscroll-contain rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg custom-scrollbar dark:border-gray-700 dark:bg-gray-900">
                                <button type="button" @click="loId = ''; dropdownOpen = false" class="flex w-full items-center rounded-lg px-3 py-2.5 text-start text-sm text-gray-500 transition hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">{{ __('Pilih Local Order (LO)') }}</button>
                                <template x-for="row in localOrders" :key="row.lo_inden_id">
                                    <button type="button" @click="selectLo(row.lo_inden_id)" class="flex w-full items-center justify-between gap-4 rounded-lg px-3 py-3 text-start transition hover:bg-brand-50 dark:hover:bg-brand-500/10">
                                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="row.lo_inden_no"></span><span class="mt-0.5 block truncate text-xs text-gray-500 dark:text-gray-400" x-text="`${row.company_name || row.customer_name} — ${row.lo_inden_date}`"></span></span>
                                        <span class="shrink-0 rounded-full bg-brand-100 px-2.5 py-1 text-theme-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">{{ __('LO') }}</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div x-cloak x-show="!lo" class="flex items-start gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                        <div><p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Belum ada LO dipilih') }}</p><p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Pilih Local Order untuk melihat pelanggan dan sebut harga yang berkaitan.') }}</p></div>
                    </div>

                    <div x-cloak x-show="lo" class="rounded-2xl border border-brand-100 bg-brand-50/50 p-5 dark:border-brand-500/20 dark:bg-brand-500/5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-brand-100 pb-4 dark:border-brand-500/20">
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">{{ __('Local Order Dipilih') }}</p><h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90" x-text="lo?.lo_inden_no || '—'"></h3><p class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="lo?.company_name || lo?.customer_name || '—'"></p></div>
                            <span class="inline-flex items-center rounded-full bg-brand-100 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">{{ __('Pelanggan') }}</span>
                        </div>
                        <div class="mt-5 overflow-hidden rounded-xl border border-brand-100 bg-white/80 dark:border-brand-500/20 dark:bg-gray-900/40">
                            <div class="border-b border-brand-100 px-4 py-3 dark:border-brand-500/20"><p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Ringkasan Rujukan') }}</p></div>
                            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-brand-100 text-sm dark:divide-brand-500/20"><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr><th scope="row" class="w-1/3 px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tarikh LO') }}</th><td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90" x-text="lo?.lo_inden_date || '—'"></td></tr>
                                <tr><th scope="row" class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('No. Sebut Harga') }}</th><td class="px-4 py-3 font-semibold text-gray-900 dark:text-white/90" x-text="lo?.quotation_no || '—'"></td></tr>
                                <tr><th scope="row" class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tarikh Sebut Harga') }}</th><td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90" x-text="lo?.quotation_date || '—'"></td></tr>
                                <tr><th scope="row" class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tajuk Sebut Harga') }}</th><td class="px-4 py-3 font-semibold text-gray-900 dark:text-white/90" x-text="lo?.quotation_title || '—'"></td></tr>
                            </tbody></table></div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800"><button type="submit" :disabled="!lo" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ $createButtonLabel }}</button></div>
                </form>
            @endif
        </x-common.document-card>
    </x-common.document-workspace>
@endsection
