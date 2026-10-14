@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title" :subtitle="__('Pilih Local Order untuk menyediakan Delivery Order kepada pelanggan.')">
        <x-slot:referenceActions>
            <a href="{{ route($backRoute) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-white font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <x-common.document-card :title="__('Langkah 1: Pilih Local Order (LO)')" :desc="__('Pilih LO untuk dimasukkan ke dalam Delivery Order.')">
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

                    <div>
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
