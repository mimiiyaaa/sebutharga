@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Tambah Baucar Bayaran')">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <form method="POST" action="{{ route('baucar-bayaran.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ selectedInvoiceIds: @js(array_map('strval', old('invoice_ids', $selectedInvoiceIds))), proceedWithoutInvoice: @js((bool) old('proceed_without_invoice')), totalAmount: @js(old('total_amount', $customerInvoices->sum('total_amount'))), get selectedTotal() { return this.selectedInvoiceIds.reduce((total, id) => { const invoice = this.invoices.find(item => String(item.id) === String(id)); return total + (invoice ? Number(invoice.total_amount || 0) : 0); }, 0); }, invoices: @js($customerInvoices), formatAmount(value) { return Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } }" x-effect="if (selectedInvoiceIds.length > 0 && !proceedWithoutInvoice) totalAmount = selectedTotal.toFixed(2)">
            @csrf
            @foreach ($selectedInvoiceIds as $invoiceId)<input type="hidden" name="invoice_ids[]" value="{{ $invoiceId }}">@endforeach
            <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
                <x-common.document-card :title="__('Maklumat Baucar')" :desc="__('Masukkan maklumat ringkas sebelum upload fail baucar bayaran.')" class="order-2 lg:order-2">
                    <div class="mb-5 border-b border-gray-100 pb-5 dark:border-gray-800">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $customerInvoices->isNotEmpty() ? __('Invoice Pelanggan Dipilih') : __('Tanpa Invoice') }}</p>
                        </div>
                        @if ($customerInvoices->isNotEmpty())
                            <div class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
                                @foreach ($customerInvoices as $invoice)
                                    <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-sm">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $invoice->document_no }} · {{ $invoice->customer_name ?: '—' }}</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">RM {{ number_format((float) $invoice->total_amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Baucar ini akan diteruskan tanpa dikaitkan dengan invoice pelanggan.') }}</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div><label for="voucher_no" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('No. Baucar') }}</label><input id="voucher_no" name="voucher_no" value="{{ old('voucher_no') }}" required maxlength="100" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('voucher_no')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div>
                        <div><label for="voucher_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh') }}</label><input id="voucher_date" name="voucher_date" type="date" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('voucher_date')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div>
                        <div><label for="payee_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Dibayar Kepada') }}</label><input id="payee_name" name="payee_name" value="{{ old('payee_name') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('payee_name')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div>
                        <div><label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Cara Bayaran') }}</label><select id="payment_method" name="payment_method" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="">{{ __('Pilih cara bayaran') }}</option>@foreach(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'] as $method)<option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ __($method) }}</option>@endforeach</select>@error('payment_method')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div>
                        <div class="md:col-span-2"><label for="total_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Jumlah Dibayar (RM)') }}</label><input id="total_amount" name="total_amount" type="number" min="0" max="9999999999.99" step="0.01" x-model="totalAmount" :readonly="selectedInvoiceIds.length > 0 && !proceedWithoutInvoice" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm read-only:bg-gray-50 read-only:text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:read-only:bg-gray-800/50">@error('total_amount')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror<p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="selectedInvoiceIds.length > 0 && !proceedWithoutInvoice">{{ __('Jumlah dikira daripada invoice yang dipilih.') }}</p></div>
                        <div class="md:col-span-2"><label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Keterangan') }}</label><textarea id="description" name="description" rows="4" maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div>
                    </div>
                </x-common.document-card>
                <section class="order-1 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ fileName: '', documentUrl: '', documentIsImage: false, chooseFile(event) { const file = event.target.files[0]; if (!file) return; const acceptedTypes = ['application/pdf', 'image/jpeg', 'image/png']; if (!acceptedTypes.includes(file.type)) { event.target.value = ''; this.fileName = ''; this.documentUrl = ''; return; } if (this.documentUrl && this.documentUrl.startsWith('blob:')) URL.revokeObjectURL(this.documentUrl); this.fileName = file.name; this.documentUrl = URL.createObjectURL(file); this.documentIsImage = file.type.startsWith('image/'); } }">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Dokumen Baucar Bayaran') }}</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Upload PDF atau imej baucar bayaran asal.') }}</p></div>
                        <label for="document_file" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Upload Dokumen') }}</label>
                        <input id="document_file" name="document_file" type="file" accept="application/pdf,image/jpeg,image/png" required class="sr-only" @change="chooseFile($event)">
                    </div>
                    <div class="border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                        <p x-show="!fileName" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada fail dipilih.') }}</p>
                        <div x-cloak x-show="fileName" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300"><svg class="h-4 w-4 text-error-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2h8l4 4v16H6V2Zm7 1.5V7h3.5L13 3.5Z"/></svg><span class="truncate" x-text="fileName"></span></div>
                    </div>
                    <div class="min-h-[360px] bg-gray-100 p-3 dark:bg-gray-950/40">
                        <div x-cloak x-show="documentUrl" class="h-[360px] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700"><template x-if="documentIsImage"><img :src="documentUrl" alt="{{ __('Preview dokumen baucar bayaran') }}" class="h-full w-full object-contain"></template><template x-if="!documentIsImage"><iframe :src="documentUrl" title="{{ __('Preview PDF Baucar Bayaran') }}" class="h-full w-full"></iframe></template></div>
                        <div x-show="!documentUrl" class="flex h-[480px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-8 text-center dark:border-gray-700 dark:bg-gray-900/50"><div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M8 13h8M8 17h6"/></svg></div><h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview dokumen akan muncul di sini') }}</h3><p class="mt-2 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Upload baucar bayaran untuk melihat dokumen sebelum menyimpannya.') }}</p><label for="document_file" class="mt-5 cursor-pointer text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Pilih PDF atau imej') }}</label></div>
                    </div>
                    @error('document_file')<p class="px-5 pb-4 text-xs text-error-600">{{ $message }}</p>@enderror
                </section>
            </div>
            <div class="flex justify-end"><button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan Baucar') }}</button></div>
        </form>
    </x-common.document-workspace>
@endsection
