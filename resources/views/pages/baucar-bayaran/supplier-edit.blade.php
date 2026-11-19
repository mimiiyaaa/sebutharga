@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$title">
<x-slot:referenceActions><x-common.back-link :fallback="$document ? route('baucar-bayaran.show', $document->id) : route('baucar-bayaran.supplier')" /></x-slot:referenceActions>
@php
$inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
$labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp
    <form method="POST" action="{{ $document ? route('baucar-bayaran.supplier.update', $document->id) : route('baucar-bayaran.supplier.store') }}"
        class="space-y-5" x-data="{
            invoices: @js($invoiceOptions), suppliers: @js($suppliers), companies: @js($companies), details: @js($details),
            payments: @js($initialPayments), supplierId: @js((string)$selectedSupplierId),
            companyId: '', paymentMethod: @js(old('payment_method', $document?->payment_method ?? '')),
            get total() { return this.payments.reduce((sum, row) => sum + Math.round((Number(row.amount) || 0) * 100), 0) / 100 },
            invoice(row) { return this.invoices.find(i => String(i.id) === String(row.invoice_id)) || {} },
            get availableInvoices() { return this.invoices.filter(i => String(i.supplier_id) === String(this.supplierId)) },
            changeSupplier() { const s = this.suppliers.find(s => String(s.customer_id) === String(this.supplierId)); if (!s) return; Object.assign(this.details, { supplier_name: s.company_name, supplier_person: s.customer_name || '', supplier_address: s.address || '', supplier_phone: s.phone_no || '', supplier_email: s.email || '' }); this.payments = [{ invoice_id: '', amount: '' }]; },
            invoiceChanged(row) { row.amount = ''; const selected = this.payments.map(p => this.invoice(p)).filter(i => i.id).sort((a,b) => a.id-b.id); if (selected.length) this.details.supplier_name = selected[0].supplier_name; },
            addInvoice() { this.payments.push({ invoice_id: '', amount: '' }) },
            removeInvoice(index) { if (this.payments.length > 1) this.payments.splice(index, 1) },
            changeCompany() { const c = this.companies.find(c => String(c.company_id) === this.companyId); if(c) Object.assign(this.details, { issuer_name: c.nama_syarikat, issuer_address: c.alamat_syarikat || '', issuer_phone: c.no_telefon || '', issuer_email: c.emel || '' }); },
            money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(Number(value) || 0) }
        }">
        @csrf @if($document) @method('PUT') @endif
        <input type="hidden" name="record_payment" value="{{ $recordPayment ? 1 : 0 }}">
        @if($recordPayment)<p class="rounded-xl bg-success-50 p-4 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ __('Isi amaun yang telah dibayar. Simpan untuk mengesahkan bayaran dan mengemas kini baki invois.') }}</p>@endif
        @if($errors->any())<div role="alert" class="rounded-xl bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400"><ul class="list-disc ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <input type="hidden" name="supplier_id" value="{{ $document->supplier_id }}">
        <input type="hidden" name="supplier_name" :value="details.supplier_name">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800 sm:p-6">
                <h2 class="border-s-4 border-brand-500 ps-4 text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Maklumat Bayaran') }}</h2>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $document->voucher_no }}</span>
            </div>
            <div class="grid gap-6 p-5 lg:grid-cols-2 sm:p-6">
                <div class="space-y-4">
                    <template x-for="(row,index) in payments" :key="index">
                        <div class="space-y-4 rounded-xl border border-brand-100 bg-brand-50/40 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
                            @if($referencesLocked)
                            <input type="hidden" :name="'payments['+index+'][invoice_id]'" :value="row.invoice_id">
                            <div><p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('No. Invois Pembekal') }}</p><p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90" x-text="invoice(row).document_no"></p></div>
                            @else
                            <label class="{{ $labelClass }}">{{ __('Pilih Invois Pembekal') }}<select :name="'payments['+index+'][invoice_id]'" x-model="row.invoice_id" @change="invoiceChanged(row)" required class="{{ $inputClass }}"><option value="">{{ __('Pilih invois') }}</option><template x-for="i in availableInvoices" :key="i.id"><option :value="String(i.id)" x-text="i.document_no" :selected="String(i.id) === String(row.invoice_id)"></option></template></select></label>
                            @endif
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="invoice(row).supplier_name || details.supplier_name"></p>
                            <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between gap-3"><dt>{{ __('Jumlah Invois') }}</dt><dd class="tabular-nums" x-text="money(invoice(row).total_amount)"></dd></div>
                                <div class="flex justify-between gap-3"><dt>{{ __('Sudah Dibayar') }}</dt><dd class="tabular-nums" x-text="money(invoice(row).paid)"></dd></div>
                                <div class="flex justify-between gap-3 border-t border-brand-100 pt-2 text-gray-900 dark:border-brand-500/20 dark:text-white/90"><dt>{{ __('Baki Invois') }}</dt><dd class="font-semibold tabular-nums" x-text="money(invoice(row).balance)"></dd></div>
                            </dl>
                            <label class="{{ $labelClass }}">{{ __('Amaun Bayaran (RM)') }}<input type="number" :name="'payments['+index+'][amount]'" min="0.01" step="0.01" :max="invoice(row).available" x-model.number="row.amount" required class="{{ $inputClass }}"></label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Had Amaun Baucar Ini') }}: <span x-text="money(invoice(row).available)"></span></p>
                        </div>
                    </template>
                </div>
                <div class="space-y-5">
                    <div class="grid gap-4">
                        <label class="{{ $labelClass }}">{{ __('Tarikh Bayaran') }}<input name="voucher_date" type="date" value="{{ old('voucher_date',$document->voucher_date) }}" required class="{{ $inputClass }}"></label>
                    </div>
                    <label class="{{ $labelClass }}">{{ __('Cara Bayaran') }}<select name="payment_method" x-model="paymentMethod" required class="{{ $inputClass }}">@foreach(['Pindahan Bank','Tunai','Cek','Lain-lain'] as $method)<option value="{{ $method }}">{{ __($method) }}</option>@endforeach</select></label>
                    <label x-cloak x-show="paymentMethod === 'Cek'" class="{{ $labelClass }}">{{ __('No. Cek') }}<input name="cheque_no" value="{{ $chequeNo }}" maxlength="100" class="{{ $inputClass }}"></label>
                    <label class="{{ $labelClass }}">{{ __('Catatan (Pilihan)') }}<textarea name="description" rows="4" maxlength="5000" class="{{ $inputClass }} h-auto">{{ old('description',$document->description) }}</textarea></label>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-5 dark:border-gray-800"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Jumlah Bayaran Kali Ini') }}</p><p class="text-2xl font-semibold text-brand-600 dark:text-brand-300" x-text="money(total)"></p></div>
                </div>
            </div>
        </section>
        <details class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900" @if($errors->any()) open @endif>
            <summary class="cursor-pointer text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Maklumat Tambahan Baucar') }}</summary>
            <div class="mt-5 space-y-4">
                <label class="{{ $labelClass }}">{{ __('No. Baucar') }}<input name="voucher_no" value="{{ old('voucher_no',$nextVoucherNo) }}" required maxlength="100" class="{{ $inputClass }}"></label>
                <div class="grid gap-4 sm:grid-cols-2">@foreach(['supplier_person'=>'Untuk Perhatian','supplier_address'=>'Alamat Pembekal','supplier_phone'=>'Nombor Telefon','supplier_email'=>'E-mel'] as $field=>$label)<label class="{{ $labelClass }}">{{ __($label) }}<input name="{{ $field }}" x-model="details.{{ $field }}" class="{{ $inputClass }}"></label>@endforeach</div>
        <div class="space-y-4">
            <label class="{{ $labelClass }}">{{ __('Pilih Syarikat') }}</label><select x-model="companyId" @change="changeCompany()" class="{{ $inputClass }}"><option value="">{{ __('Pilih syarikat atau isi maklumat sendiri') }}</option>@foreach($companies as $company)<option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>@endforeach</select>
            <div class="grid gap-4 sm:grid-cols-2">@foreach(['issuer_name'=>'Nama Syarikat','issuer_address'=>'Alamat Syarikat','issuer_phone'=>'Nombor Telefon','issuer_email'=>'E-mel'] as $field=>$label)<label class="{{ $labelClass }}">{{ __($label) }}@if($field === 'issuer_address')<textarea name="{{ $field }}" x-model="details.{{ $field }}" rows="3" class="{{ $inputClass }} h-auto"></textarea>@else<input name="{{ $field }}" x-model="details.{{ $field }}" @required($field === 'issuer_name') class="{{ $inputClass }}">@endif</label>@endforeach</div>
        </div>
        <div class="mt-6"><div class="grid gap-6 md:grid-cols-2">
            @foreach([['prepared_by','prepared_role','Disediakan Oleh','Jawatan / Unit Penyedia',$preparedBy,$preparedRole],['received_by','received_role','Diterima Oleh','Jawatan / Unit Penerima',$receivedBy,$receivedRole]] as [$name,$role,$nameLabel,$roleLabel,$nameValue,$roleValue])
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30"><label class="{{ $labelClass }}">{{ __($nameLabel) }}<input name="{{ $name }}" value="{{ $nameValue }}" @required($name === 'prepared_by') maxlength="255" class="{{ $inputClass }}"></label><label class="{{ $labelClass }}">{{ __($roleLabel) }}<input name="{{ $role }}" value="{{ $roleValue }}" @required($role === 'prepared_role') maxlength="255" class="{{ $inputClass }}"></label><div class="h-16" aria-hidden="true"></div><div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div></div>
            @endforeach
        </div></div>
            </div>
        </details>
        <div class="flex justify-end gap-3"><a class="document-action" href="{{ route('baucar-bayaran.show',$document->id) }}">{{ __('Batal') }}</a><button type="submit" class="document-action document-action-primary">{{ __($recordPayment ? 'Sahkan dan Simpan Bayaran' : 'Simpan Perubahan') }}</button></div>
    </form>
</x-common.document-workspace>
@endsection
