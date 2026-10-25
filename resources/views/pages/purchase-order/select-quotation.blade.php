@extends('layouts.app')

@section('content')
    <x-common.document-workspace class="purchase-order-select" :title="__('Tambah Purchase Order')" :subtitle="__('Pilih sebut harga pembekal untuk dijadikan Purchase Order.')">
        <x-slot:referenceActions><a href="{{ route('purchase-order.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <form method="GET" action="{{ route('purchase-order.form') }}" class="space-y-6" @submit.prevent="if (!quotation) { selectionError = true; return; } $el.submit()" x-data="{ quotationId: {{ Illuminate\Support\Js::from((string) old('supplier_quotation_id', '')) }}, selectedQuotation: null, selectionError: false, quotations: {{ Illuminate\Support\Js::from($quotations) }}, init() { this.selectedQuotation = this.quotations.find(row => String(row.supplier_quotation_id) === String(this.quotationId)) || null; }, get quotation() { return this.selectedQuotation; }, selectQuotation(id) { this.quotationId = String(id); this.selectedQuotation = this.quotations.find(row => String(row.supplier_quotation_id) === String(id)) || null; this.selectionError = false; }, formatMoney(value) { return Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } }">
            @if ($errors->any())<p role="alert" class="rounded-xl bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">{{ __('Sila pilih sebut harga pembekal.') }}</p>@endif
            <x-common.document-card :title="__('Pilih Sebut Harga Pembekal')" :desc="__('Pilih satu sebut harga pembekal untuk dimasukkan ke dalam Purchase Order.')">
                @if ($quotations->isEmpty())
                    <p class="rounded-xl bg-peach-100 p-4 text-sm text-peach-700 dark:bg-peach-500/15 dark:text-peach-300">{{ __('Belum ada sebut harga pembekal.') }}</p>
                @else
                    <input type="hidden" name="supplier_quotation_id" x-model="quotationId">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400"><tr><th class="w-12 px-4 py-3"></th><th class="px-4 py-3 text-start">{{ __('No. Sebut Harga') }}</th><th class="px-4 py-3 text-start">{{ __('Pembekal') }}</th><th class="px-4 py-3 text-start">{{ __('Tajuk') }}</th><th class="px-4 py-3 text-start">{{ __('Tarikh') }}</th><th class="px-4 py-3 text-end">{{ __('Jumlah (RM)') }}</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="row in quotations" :key="row.supplier_quotation_id">
                                    <tr @click="selectQuotation(row.supplier_quotation_id)" :class="String(quotationId) === String(row.supplier_quotation_id) ? 'bg-brand-50 dark:bg-brand-500/10' : 'hover:bg-gray-50/70 dark:hover:bg-gray-800/40'" class="cursor-pointer transition">
                                        <td class="px-4 py-3 text-center"><input type="radio" name="quotation_choice" :value="row.supplier_quotation_id" :checked="String(quotationId) === String(row.supplier_quotation_id)" @click.stop="selectQuotation(row.supplier_quotation_id)" class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200" x-text="row.quotation_no"></td>
                                        <td class="min-w-52 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.company_name || row.customer_name || '—'"></td>
                                        <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_title || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.quotation_date || '—'"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-end font-medium text-gray-800 dark:text-gray-200" x-text="'RM ' + formatMoney(row.jumlah_total)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p x-cloak x-show="selectionError" class="mt-3 text-sm text-error-600 dark:text-error-400">{{ __('Sila pilih satu sebut harga pembekal.') }}</p>
                    <div class="mt-4 flex justify-end"><button type="submit" :disabled="!quotation" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Teruskan ke Borang Purchase Order') }}</button></div>
                @endif
            </x-common.document-card>
        </form>
    </x-common.document-workspace>
@endsection
