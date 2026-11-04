@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$title" :subtitle="__($standalone ? 'Baucar Standalone — tanpa rujukan invois.' : ($referencesLocked ? 'Invois rujukan telah ditetapkan. Isi amaun dan butiran bayaran, kemudian simpan baucar.' : 'Pilih invois pembekal, isi amaun dan cara bayaran, kemudian simpan baucar.'))">
<x-slot:referenceActions><x-common.back-link :fallback="$document ? route('baucar-bayaran.show', $document->id) : route('baucar-bayaran.supplier')" /></x-slot:referenceActions>
@php
$inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
$labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp
    <form method="POST" action="{{ $document ? route('baucar-bayaran.supplier.update', $document->id) : route('baucar-bayaran.supplier.store') }}"
        class="space-y-5" x-data="{
            invoices: @js($invoiceOptions), suppliers: @js($suppliers), companies: @js($companies), details: @js($details),
            payments: @js($initialPayments), supplierId: @js((string)$selectedSupplierId),
            standalone: @js($standalone), manualItems: @js($initialManualItems), companyId: '', paymentMethod: @js(old('payment_method', $document?->payment_method ?? '')),
            get total() { return (this.standalone ? this.manualItems : this.payments).reduce((sum, row) => sum + Math.round((Number(row.amount) || 0) * 100), 0) / 100 },
            invoice(row) { return this.invoices.find(i => String(i.id) === String(row.invoice_id)) || {} },
            get availableInvoices() { return this.invoices.filter(i => String(i.supplier_id) === String(this.supplierId) && Number(i.balance) > 0 && Number(i.available) > 0) },
            changeSupplier() { const s = this.suppliers.find(s => String(s.customer_id) === String(this.supplierId)); if (!s) return; Object.assign(this.details, { supplier_name: s.company_name, supplier_person: s.customer_name || '', supplier_address: s.address || '', supplier_phone: s.phone_no || '', supplier_email: s.email || '' }); this.payments = [{ invoice_id: '', amount: '' }]; },
            invoiceChanged(row) { row.amount = ''; const selected = this.payments.map(p => this.invoice(p)).filter(i => i.id).sort((a,b) => a.id-b.id); if (selected.length) this.details.supplier_name = selected[0].supplier_name; },
            addInvoice() { this.payments.push({ invoice_id: '', amount: '' }) },
            removeInvoice(index) { if (this.payments.length > 1) this.payments.splice(index, 1) },
            changeCompany() { const c = this.companies.find(c => String(c.company_id) === this.companyId); if(c) Object.assign(this.details, { issuer_name: c.nama_syarikat, issuer_address: c.alamat_syarikat || '', issuer_phone: c.no_telefon || '', issuer_email: c.emel || '' }); },
            money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(Number(value) || 0) }
        }">
        <input type="hidden" name="source_type" value="{{ $standalone ? 'standalone' : 'invoice' }}">
        @csrf @if($document) @method('PUT') @endif
        <input type="hidden" name="record_payment" value="{{ $recordPayment ? 1 : 0 }}">
        @if($recordPayment)<p class="rounded-xl bg-success-50 p-4 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ __('Isi amaun yang telah dibayar. Simpan untuk mengesahkan bayaran dan mengemas kini baki invois.') }}</p>@endif
        @if($errors->any())<div role="alert" class="rounded-xl bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400"><ul class="list-disc ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <p class="rounded-xl bg-brand-50 p-4 text-sm text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __($standalone ? 'Isi penerima, tujuan dan amaun bayaran. Baucar ini tidak memerlukan invois.' : ($referencesLocked ? 'Invois rujukan telah ditetapkan. Isi amaun dan butiran bayaran, kemudian simpan baucar.' : '1. Pilih pembekal dan invois. 2. Isi amaun dan cara bayaran. 3. Simpan baucar.')) }}</p>
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card :title="__('Maklumat Baucar')" class="self-start">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="{{ $labelClass }}">{{ __('No. Baucar') }}<input name="voucher_no" value="{{ old('voucher_no', $nextVoucherNo) }}" required maxlength="100" class="{{ $inputClass }}"></label>
                <label class="{{ $labelClass }}">{{ __('Tarikh Baucar') }}<input name="voucher_date" type="date" value="{{ old('voucher_date', $document?->voucher_date ?? now()->format('Y-m-d')) }}" required class="{{ $inputClass }}"></label>
            </div>
        </x-common.document-card>
        <x-common.document-card :title="__('Kepada: Pembekal')">
            @if($referencesLocked)<input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">@endif
            <label class="{{ $labelClass }}">{{ __('Pilih Pembekal') }}</label>
            <select name="supplier_id" x-model="supplierId" @change="changeSupplier()" @disabled($referencesLocked) @required(!$standalone) class="{{ $inputClass }}"><option value="">{{ __($standalone ? 'Isi penerima sendiri atau pilih pembekal' : 'Pilih Pembekal') }}</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->customer_id }}">{{ $supplier->company_name }}</option>@endforeach</select>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['supplier_name'=>'Nama Pembekal / Syarikat','supplier_person'=>'Untuk Perhatian','supplier_phone'=>'Nombor Telefon','supplier_email'=>'E-mel','supplier_address'=>'Alamat Pembekal'] as $field=>$label)
                <label class="{{ $labelClass }}">{{ __($label) }}@if($field === 'supplier_address')<textarea name="{{ $field }}" x-model="details.{{ $field }}" rows="3" class="{{ $inputClass }} h-auto"></textarea>@else<input name="{{ $field }}" x-model="details.{{ $field }}" @readonly($field === 'supplier_name' && !$standalone) @required($field === 'supplier_name') class="{{ $inputClass }}">@endif</label>
                @endforeach
            </div>
        </x-common.document-card></div>
        @if($standalone)
        <x-common.document-card :title="__('Butiran Bayaran')">
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Isi keterangan dan amaun bagi setiap bayaran. Tiada rujukan invois diperlukan.') }}</p>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800"><tr>
                        <th class="w-14 p-3 text-center">{{ __('Bil.') }}</th>
                        <th class="p-3 text-start">{{ __('Butiran Pembayaran') }}</th><th class="w-36 p-3 text-center">{{ __('No. Invois Pembekal') }}</th>
                        <th class="w-56 p-3 text-start">{{ __('Jumlah (RM)') }}</th>
                        <th class="w-24 p-3 text-center">{{ __('Tindakan') }}</th>
                    </tr></thead>
                    <tbody><template x-for="(row,index) in manualItems" :key="index"><tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="p-3 text-center" x-text="index + 1"></td>
                        <td class="p-3"><textarea :name="'manual_items['+index+'][description]'" x-model="row.description" required maxlength="5000" rows="2" aria-label="{{ __('Keterangan Bayaran') }}" class="{{ $inputClass }} h-auto"></textarea></td>
                        <td class="p-3 text-center text-gray-400 dark:text-gray-500">—</td><td class="p-3"><input :name="'manual_items['+index+'][amount]'" x-model="row.amount" type="number" required min="0.01" step="0.01" max="9999999999.99" aria-label="{{ __('Amaun Bayaran (RM)') }}" class="{{ $inputClass }} tabular-nums"></td>
                        <td class="p-3 text-center"><button type="button" @click="manualItems.splice(index, 1)" :disabled="manualItems.length === 1" class="document-action document-action-delete disabled:opacity-40" aria-label="{{ __('Buang') }}"><x-svg.item-trash /></button></td>
                    </tr></template></tbody>
                </table>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <button type="button" @click="manualItems.push({description: '', amount: ''})" :disabled="manualItems.length >= 100" class="document-action disabled:opacity-40">{{ __('Tambah Butiran Bayaran') }}</button>
                <p class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Jumlah Bayaran Kali Ini') }}: <span class="tabular-nums" x-text="money(total)"></span></p>
            </div>
        </x-common.document-card>
        @else
        <x-common.document-card :title="__('Invois Yang Dibayar')">
            @if(!$referencesLocked)<p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih invois dan isi amaun bayaran kali ini. Anda boleh bayar sebahagian atau bayar penuh.') }}</p>@endif
            <div x-show="!supplierId" class="mb-4 text-sm text-warning-700 dark:text-warning-400">{{ __('Pilih pembekal di atas terlebih dahulu.') }}</div>
            <div x-cloak x-show="supplierId && !availableInvoices.length" class="mb-4 text-sm text-warning-700 dark:text-warning-400">{{ __('Tiada invois untuk pembekal ini.') }} <a class="underline" href="{{ route('invoice.supplier.create') }}">{{ __('Tambah Invois Pembekal') }}</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-start text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach(array_merge(['Invois','Jumlah Invois','Baki Invois','Bayaran Kali Ini (RM)'], $referencesLocked ? [] : ['Tindakan']) as $heading)<th class="p-3 text-start">{{ __($heading) }}</th>@endforeach</tr></thead>
                <tbody><template x-for="(row,index) in payments" :key="index"><tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="p-3">@if($referencesLocked)<input type="hidden" :name="'payments['+index+'][invoice_id]'" :value="row.invoice_id"><span class="font-semibold text-gray-800 dark:text-white/90" x-text="invoice(row).document_no"></span>@else<select :name="'payments['+index+'][invoice_id]'" x-model="row.invoice_id" @change="invoiceChanged(row)" required class="{{ $inputClass }}"><option value="">{{ __('Pilih invois') }}</option><template x-for="i in availableInvoices" :key="i.id"><option :value="String(i.id)" x-text="i.document_no"></option></template></select>@endif</td>
                    <td class="p-3 tabular-nums" x-text="row.invoice_id ? money(invoice(row).total_amount) : '-' "></td>
                    <td class="p-3 tabular-nums" x-text="row.invoice_id ? money(invoice(row).balance) : '-' "></td>
                    <td class="p-3"><input :name="'payments['+index+'][amount]'" type="number" min="0.01" step="0.01" :max="invoice(row).available" x-model.number="row.amount" required class="{{ $inputClass }}"><p x-show="row.invoice_id" class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Had Amaun Baucar Ini') }}: <span x-text="money(invoice(row).available)"></span></p></td>
                    @if(!$referencesLocked)<td class="p-3"><button type="button" @click="removeInvoice(index)" :disabled="payments.length === 1" class="document-action document-action-delete disabled:opacity-40" aria-label="{{ __('Buang') }}"><x-svg.item-trash /></button></td>@endif
                </tr></template></tbody>
            </table></div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">@if(!$referencesLocked)<button type="button" @click="addInvoice()" class="document-action">{{ __('Tambah Invois Lain') }}</button>@endif<p class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Jumlah Bayaran Kali Ini') }}: <span x-text="money(total)"></span></p></div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ __('Baki invois hanya menolak bayaran yang disahkan. Had amaun turut mengambil kira baucar lain yang belum dibayar.') }}</p>
        </x-common.document-card>
        @endif
        <x-common.document-card :title="__('Maklumat Bayaran')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="{{ $labelClass }}">{{ __('Cara Bayaran') }}<select name="payment_method" x-model="paymentMethod" required class="{{ $inputClass }}"><option value="">{{ __('Pilih cara bayaran') }}</option>@foreach(['Tunai','Pindahan Bank','Cek','Lain-lain'] as $method)<option value="{{ $method }}">{{ __($method) }}</option>@endforeach</select></label>
                <label class="{{ $labelClass }}">{{ __('Amaun Baucar') }}<input name="total_amount" type="number" :value="total.toFixed(2)" readonly class="{{ $inputClass }}"></label>
                <label x-cloak x-show="paymentMethod === 'Cek'" class="{{ $labelClass }}">{{ __('No. Cek') }}<input name="cheque_no" value="{{ $chequeNo }}" maxlength="100" class="{{ $inputClass }}"></label>
                @if(!$standalone)<label class="{{ $labelClass }} sm:col-span-2">{{ __('Keterangan') }}<textarea name="description" rows="5" maxlength="5000" class="{{ $inputClass }} h-auto">{{ old('description', $document?->description) }}</textarea></label>@endif
            </div>
        </x-common.document-card>
        <x-common.document-card :title="__('Maklumat Syarikat')">
            <label class="{{ $labelClass }}">{{ __('Pilih Syarikat') }}</label><select x-model="companyId" @change="changeCompany()" class="{{ $inputClass }}"><option value="">{{ __('Pilih syarikat atau isi maklumat sendiri') }}</option>@foreach($companies as $company)<option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>@endforeach</select>
            <div class="grid gap-4 sm:grid-cols-2">@foreach(['issuer_name'=>'Nama Syarikat','issuer_address'=>'Alamat Syarikat','issuer_phone'=>'Nombor Telefon','issuer_email'=>'E-mel'] as $field=>$label)<label class="{{ $labelClass }}">{{ __($label) }}@if($field === 'issuer_address')<textarea name="{{ $field }}" x-model="details.{{ $field }}" rows="3" class="{{ $inputClass }} h-auto"></textarea>@else<input name="{{ $field }}" x-model="details.{{ $field }}" @required($field === 'issuer_name') class="{{ $inputClass }}">@endif</label>@endforeach</div>
        </x-common.document-card>
        <x-common.document-card :title="__('Pengesahan Baucar')"><div class="grid gap-6 md:grid-cols-2">
            @foreach([['prepared_by','prepared_role','Disediakan Oleh','Jawatan / Unit Penyedia',$preparedBy,$preparedRole],['received_by','received_role','Diterima Oleh','Jawatan / Unit Penerima',$receivedBy,$receivedRole]] as [$name,$role,$nameLabel,$roleLabel,$nameValue,$roleValue])
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30"><label class="{{ $labelClass }}">{{ __($nameLabel) }}<input name="{{ $name }}" value="{{ $nameValue }}" @required($name === 'prepared_by') maxlength="255" class="{{ $inputClass }}"></label><label class="{{ $labelClass }}">{{ __($roleLabel) }}<input name="{{ $role }}" value="{{ $roleValue }}" @required($role === 'prepared_role') maxlength="255" class="{{ $inputClass }}"></label><div class="h-16" aria-hidden="true"></div><div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div></div>
            @endforeach
        </div></x-common.document-card>
        <div class="flex flex-wrap justify-end gap-3"><a class="document-action" href="{{ $document ? route('baucar-bayaran.show',$document->id) : route('baucar-bayaran.supplier') }}">{{ __('Batal') }}</a><button class="document-action document-action-primary" type="submit">{{ __($recordPayment ? 'Sahkan dan Simpan Bayaran' : 'Simpan Baucar') }}</button></div>
    </form>
</x-common.document-workspace>
@endsection
