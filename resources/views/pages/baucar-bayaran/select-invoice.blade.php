@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <form method="GET" action="{{ route('baucar-bayaran.form') }}" class="space-y-6" x-data="{ selectedInvoiceIds: [], invoices: @js($customerInvoices), formatAmount(value) { return Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }, get selectedTotal() { return this.selectedInvoiceIds.reduce((total, id) => { const invoice = this.invoices.find(item => String(item.id) === String(id)); return total + (invoice ? Number(invoice.balance_amount || 0) : 0); }, 0); } }">
            <x-common.document-card :title="__('Pilih Invoice Kepada Pelanggan')" :desc="__('Tick satu atau lebih invoice yang mahu dikaitkan dengan baucar bayaran.')">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                            <tr>
                                <th class="w-12 px-4 py-3"></th>
                                <th class="px-4 py-3">{{ __('No. Invoice') }}</th>
                                <th class="px-4 py-3">{{ __('Tajuk Invoice') }}</th>
                                <th class="px-4 py-3">{{ __('Pelanggan') }}</th>
                                <th class="px-4 py-3">{{ __('Tarikh') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Baki (RM)') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($customerInvoices as $invoice)
                                <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                    <td class="px-4 py-3 align-middle"><input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}" x-model="selectedInvoiceIds" @disabled($invoice->balance_amount <= 0 || $invoice->payment_status === __('Perlu Semakan')) class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"></td>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><a href="{{ route('invoice.customer.payments', $invoice->id) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $invoice->document_no }}</a></td>
                                    <td class="min-w-48 px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->document_title ?: '—' }}</td>
                                    <td class="min-w-52 px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->customer_name ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->document_date ? \Carbon\Carbon::parse($invoice->document_date)->format('d/m/Y') : '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-800 dark:text-gray-200">RM {{ number_format((float) $invoice->balance_amount, 2) }}<span class="block text-theme-xs">{{ $invoice->payment_status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Tiada invoice pelanggan tersedia.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/50">
                    <p class="text-sm text-gray-600 dark:text-gray-300"><span x-text="selectedInvoiceIds.length"></span> {{ __('invoice dipilih') }} · <span class="font-semibold text-gray-800 dark:text-white/90" x-text="'RM ' + formatAmount(selectedTotal)"></span></p>
                    <div class="flex flex-wrap gap-2">

                        <a href="{{ route('baucar-bayaran.form', ['tanpa_invoice' => 1]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Teruskan Tanpa Invoice') }}</a>
                        <button type="submit" :disabled="selectedInvoiceIds.length === 0" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Teruskan ke Borang') }}</button>
                    </div>
                </div>
            </x-common.document-card>
        </form>
    </x-common.document-workspace>
@endsection
