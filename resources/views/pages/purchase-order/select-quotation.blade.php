@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Tambah Purchase Order')" :subtitle="__('Pilih sebut harga yang telah diluluskan untuk dijadikan Purchase Order.')">
        <x-slot:referenceActions>
            <a href="{{ route('purchase-order.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <x-common.document-card :title="__('Langkah 1: Pilih Sebut Harga')" :desc="__('Hanya sebut harga yang telah dipersetujui boleh digunakan untuk Purchase Order.')">
            @if ($errors->any())
                <p role="alert" class="rounded-xl bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Sila pilih sebut harga yang telah dipersetujui.') }}</p>
            @endif

            @if ($quotations->isEmpty())
                <p class="rounded-xl bg-peach-100 p-4 text-sm text-peach-700 dark:bg-peach-500/15 dark:text-peach-300">{{ __('Belum ada sebut harga yang telah dipersetujui.') }}</p>
            @else
                <form method="GET" action="{{ route('purchase-order.form') }}" class="space-y-5" x-data="{ quotationId: {{ Illuminate\Support\Js::from((string) old('quotation_detail_id', '')) }}, dropdownOpen: false, quotations: {{ Illuminate\Support\Js::from($quotations) }}, get quotation() { return this.quotations.find(row => String(row.quotation_detail_id) === String(this.quotationId)); }, selectQuotation(id) { this.quotationId = String(id); this.dropdownOpen = false; }, formatMoney(value) { return Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }, formatDate(value) { if (!value) return '—'; return new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(`${value}T00:00:00`)); } }">
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/70 px-4 py-3 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-xs font-bold text-white">1</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ __('Pilih dokumen rujukan') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Maklumat item akan dimasukkan secara automatik ke dalam PO.') }}</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-brand-700 shadow-theme-xs dark:bg-gray-900 dark:text-brand-300">{{ __('Langkah 1 daripada 2') }}</span>
                    </div>
                    <div>
                        <label for="quotation_detail_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Sebut Harga') }} <span class="text-error-500">*</span></label>
                        <input type="hidden" name="quotation_detail_id" x-model="quotationId">
                        <div class="relative w-full" @click.outside="dropdownOpen = false">
                            <button type="button" id="quotation_detail_id" @click="dropdownOpen = !dropdownOpen" :aria-expanded="dropdownOpen" class="flex min-h-12 w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-start text-sm text-gray-800 outline-none transition hover:border-brand-300 hover:bg-white focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90 dark:hover:border-brand-500/50 dark:hover:bg-gray-800 dark:focus:bg-gray-900">
                                <span class="truncate" :class="quotation ? 'font-medium text-gray-900 dark:text-white/90' : 'text-gray-500 dark:text-gray-400'" x-text="quotation ? `${quotation.quotation_no} — ${quotation.company_name || quotation.customer_name}` : '{{ __('Pilih sebut harga yang dipersetujui') }}'"></span>
                                <svg class="h-4 w-4 shrink-0 text-gray-500 transition-transform dark:text-gray-400" :class="dropdownOpen ? 'rotate-180 text-brand-500' : ''" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-cloak x-show="dropdownOpen" x-transition class="absolute start-0 top-full z-20 mt-2 w-full max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg sm:min-w-[36rem] dark:border-gray-700 dark:bg-gray-900">
                                <button type="button" @click="quotationId = ''; dropdownOpen = false" class="flex w-full items-center rounded-lg px-3 py-2.5 text-start text-sm text-gray-500 transition hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">{{ __('Pilih sebut harga yang dipersetujui') }}</button>
                                <template x-for="row in quotations" :key="row.quotation_detail_id">
                                    <button type="button" @click="selectQuotation(row.quotation_detail_id)" class="flex w-full items-center justify-between gap-4 rounded-lg px-3 py-3 text-start transition hover:bg-brand-50 dark:hover:bg-brand-500/10">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-gray-800 dark:text-white/90" x-text="row.quotation_no"></span>
                                            <span class="mt-0.5 block truncate text-xs text-gray-500 dark:text-gray-400" x-text="row.company_name || row.customer_name"></span>
                                        </span>
                                        <span class="shrink-0 rounded-full bg-success-100 px-2.5 py-1 text-theme-xs font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-400">{{ __('Dipersetujui') }}</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div x-cloak x-show="!quotation" class="flex items-start gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-500 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400">
                            <svg class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h4m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l4 4v12a2 2 0 0 1-2 2Zm-2-18v4h4"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Belum ada sebut harga dipilih') }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Pilih sebut harga yang telah dipersetujui untuk melihat maklumat pelanggan, item dan jumlah keseluruhan.') }}</p>
                        </div>
                    </div>

                    <div x-cloak x-show="quotation" class="rounded-2xl border border-brand-100 bg-brand-50/50 p-5 dark:border-brand-500/20 dark:bg-brand-500/5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-brand-100 pb-4 dark:border-brand-500/20">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">{{ __('Sebut Harga Dipilih') }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90" x-text="quotation?.quotation_no || '—'"></h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="quotation?.company_name || quotation?.customer_name || '—'"></p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-success-100 px-3 py-1.5 text-xs font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-400">{{ __('Dipersetujui') }}</span>
                        </div>
                        <div class="mt-5 overflow-hidden rounded-xl border border-brand-100 bg-white/80 dark:border-brand-500/20 dark:bg-gray-900/40">
                            <div class="border-b border-brand-100 px-4 py-3 dark:border-brand-500/20">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Ringkasan Sebut Harga') }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-brand-100 text-sm dark:divide-brand-500/20">
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr>
                                            <th scope="row" class="w-1/3 px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tajuk') }}</th>
                                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white/90" x-text="quotation?.quotation_title || '—'"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</th>
                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90" x-text="formatDate(quotation?.quotation_date)"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Bilangan Item') }}</th>
                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90" x-text="`${quotation?.item_count || 0} item`"></td>
                                        </tr>
                                        <tr class="bg-brand-50/40 dark:bg-brand-500/5">
                                            <th scope="row" class="px-4 py-3 text-start text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Jumlah Sebut Harga') }}</th>
                                            <td class="px-4 py-3 text-base font-bold tabular-nums text-brand-700 dark:text-brand-300" x-text="`RM ${formatMoney(quotation?.jumlah_total)}`"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-5 overflow-hidden rounded-xl border border-brand-100 bg-white/80 dark:border-brand-500/20 dark:bg-gray-900/40">
                            <div class="border-b border-brand-100 px-4 py-3 dark:border-brand-500/20">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Senarai Item Sebut Harga') }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <th class="w-14 px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Bil') }}</th>
                                            <th class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Perihal Barangan') }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Kuantiti') }}</th>
                                            <th class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Unit') }}</th>
                                            <th class="px-4 py-3 text-end text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Harga Seunit (RM)') }}</th>
                                            <th class="px-4 py-3 text-end text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Jumlah (RM)') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="(item, index) in (quotation?.items || [])" :key="`${quotation.quotation_detail_id}-${index}`">
                                            <tr class="hover:bg-brand-50/40 dark:hover:bg-brand-500/5">
                                                <td class="px-4 py-3 text-center tabular-nums text-gray-400 dark:text-gray-500" x-text="index + 1"></td>
                                                <td class="whitespace-pre-line px-4 py-3 font-medium leading-6 text-gray-800 dark:text-gray-200" x-text="item.item_description"></td>
                                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300" x-text="item.quantity"></td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="item.unit"></td>
                                                <td class="px-4 py-3 text-end tabular-nums text-gray-600 dark:text-gray-300" x-text="formatMoney(item.unit_price)"></td>
                                                <td class="px-4 py-3 text-end font-semibold tabular-nums text-gray-900 dark:text-white/90" x-text="formatMoney(item.subtotal)"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="quotation && !(quotation.items || []).length">
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada item') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <p x-cloak x-show="quotationId && !quotation" class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800/50 dark:text-gray-400">{{ __('Sebut harga yang dipilih tidak lagi tersedia.') }}</p>

                    <div class="flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="submit" :disabled="!quotation" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Teruskan ke Purchase Order') }}</button>
                    </div>
                </form>
            @endif
        </x-common.document-card>
    </x-common.document-workspace>
@endsection
