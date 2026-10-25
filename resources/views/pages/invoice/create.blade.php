@extends('layouts.app')
@section('content')
@php($values = $values ?? [])
@php($editId = $editId ?? null)
<x-common.document-workspace :title="$title">
    <x-slot:referenceActions><a href="{{ ($editId ? route('invoice.customer.show', $editId) : route('invoice.customer.create')) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
    <form method="POST" action="{{ ($editId ? route('invoice.customer.update', $editId) : route('invoice.customer.store')) }}" class="space-y-6" x-data="{
        companies: @js($companies), customers: @js($customers), localOrders: @js($localOrders ?? []),
        customerId: @js((string) old('customer_id', $values['customer_id'] ?? '')),
        issuer: @js(['name'=>old('issuer_name', $values['issuer_name'] ?? config('purchase_order.issuer_name')), 'address'=>old('issuer_address', $values['issuer_address'] ?? config('purchase_order.issuer_address')), 'phone'=>old('issuer_phone', $values['issuer_phone'] ?? config('purchase_order.issuer_phone')), 'email'=>old('issuer_email', $values['issuer_email'] ?? config('purchase_order.issuer_email'))]),
        customer: @js(['name'=>old('customer_name', $values['customer_name'] ?? ''), 'address'=>old('customer_address', $values['customer_address'] ?? '')]),
        signatureCompany: @js(old('signature_company', $values['signature_company'] ?? null)),
        items: @js(collect(old('items', $initialItems ?? [['description'=>'', 'quantity'=>1, 'unit'=>'Unit', 'price'=>0]]))->values()->map(fn($item,$index)=>array_merge($item,['id'=>$index+1]))),
        nextId: {{ count(old('items', $initialItems ?? [1])) + 1 }},
        selectCompany(id) { const c = this.companies.find(c => String(c.company_id) === id); if(c) this.issuer = {name:c.nama_syarikat, address:c.alamat_syarikat || '', phone:c.no_telefon || '', email:c.emel || ''}; },
        selectCustomer(id) { const c = this.customers.find(c => String(c.customer_id) === id); if(c) this.customer = {name:c.company_name || c.customer_name, address:c.address || ''}; },
        selectLo(id) { const lo = this.localOrders.find(lo => String(lo.lo_inden_id) === String(id)); if (lo) { this.customerId = String(lo.customer_id); this.selectCustomer(this.customerId); } },
        addItem() { this.items.push({id:this.nextId++, description:'', quantity:1, unit:'Unit', price:0}); },
        subtotal(item) { return Math.round(Math.max(0, Number(item.quantity)||0) * Math.max(0, Number(item.price)||0)*100)/100; },
        get gross() { return this.items.reduce((sum,item)=>sum+this.subtotal(item),0); },
        money(value) { return new Intl.NumberFormat('en-MY',{style:'currency',currency:'MYR'}).format(value); }
    }">
        @csrf
        @if(!$editId && !empty($values['lo_inden_id']))<input type="hidden" name="lo_inden_id" value="{{ $values['lo_inden_id'] }}">@endif
        @if($editId) @method('PUT') @endif
        @if ($errors->any())
            <ul role="alert" class="rounded-xl bg-error-50 p-5 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        <div class="grid gap-6 xl:grid-cols-2">
            <x-common.document-card :title="__('Maklumat Syarikat')" :desc="__('Kepala surat di bahagian atas invois.')">
                <div><label for="company_id" class="mb-1.5 block text-sm">{{ __('Pilih syarikat') }}</label><select id="company_id" @change="selectCompany($event.target.value)" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 dark:border-gray-700 dark:bg-gray-900"><option value="">{{ __('Pilih syarikat') }}</option>@foreach($companies as $company)<option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>@endforeach</select></div>
                <x-supplier-document.field name="issuer_name" :label="__('Nama Syarikat')" x-model="issuer.name" required maxlength="255" :value="$values['issuer_name'] ?? ''" />
                <x-supplier-document.field name="issuer_address" :label="__('Alamat Syarikat')" type="textarea" x-model="issuer.address" required maxlength="2000" :value="$values['issuer_address'] ?? ''" />
                <div class="grid gap-5 sm:grid-cols-2"><x-supplier-document.field name="issuer_phone" :label="__('Nombor Telefon')" x-model="issuer.phone" type="tel" :value="$values['issuer_phone'] ?? ''" /><x-supplier-document.field name="issuer_email" :label="__('E-mel')" x-model="issuer.email" type="email" :value="$values['issuer_email'] ?? ''" /></div>
            </x-common.document-card>
            <x-common.document-card :title="__('Maklumat Invois')">
                <x-supplier-document.field name="invoice_no" :label="__('No. Dokumen')" :value="$values['invoice_no'] ?? $nextNumber" required maxlength="100" pattern="[A-Za-z0-9._\-]+" />
                <x-supplier-document.field name="invoice_date" :label="__('Tarikh Invois')" type="date" :value="$values['invoice_date'] ?? now()->format('Y-m-d')" required />
                <x-supplier-document.field name="invoice_title" :label="__('Tajuk Invois')" :value="$values['invoice_title'] ?? ''" maxlength="255" />
                @if($editId)
                    <div>
                        <label for="lo_inden_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('No. Local Order (LO)') }}</label>
                        <select id="lo_inden_id" name="lo_inden_id" @change="selectLo($event.target.value)" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">{{ __('Pilih Local Order (LO)') }}</option>
                            @foreach($localOrders as $lo)
                                <option value="{{ $lo->lo_inden_id }}" @selected((string) old('lo_inden_id', $values['lo_inden_id'] ?? '') === (string) $lo->lo_inden_id)>{{ $lo->lo_inden_no }} — {{ $lo->company_name ?: $lo->customer_name }}</option>
                            @endforeach
                        </select>
                        @if(empty($values['lo_inden_id']) && !empty($values['lo_reference']))<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Rujukan sedia ada:') }} {{ $values['lo_reference'] }}</p>@endif
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Pelanggan dan alamat akan mengikut LO yang dipilih. Item sedia ada dikekalkan.') }}</p>
                    </div>
                @else
                <x-supplier-document.field name="lo_reference" :label="__('No. Rujukan LO (pilihan)')" maxlength="100" :value="$values['lo_reference'] ?? ''" />
                @endif
                <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Invois dijana dalam format A4 dengan kepala surat, butiran bil, item, arahan pembayaran dan tandatangan.') }}</p>
            </x-common.document-card>
        </div>
        <x-common.document-card :title="__('Kepada / Bill To:')">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-5">
                    <div><label for="customer_id" class="mb-1.5 block text-sm">{{ __('Pilih Pelanggan') }}</label><select id="customer_id" name="customer_id" x-model="customerId" required @change="selectCustomer($event.target.value)" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 dark:border-gray-700 dark:bg-gray-900"><option value="">{{ __('Pilih Pelanggan') }}</option>@foreach($customers as $customer)<option value="{{ $customer->customer_id }}" @selected((string)old('customer_id', $values['customer_id'] ?? null) === (string)$customer->customer_id)>{{ $customer->company_name ?: $customer->customer_name }}</option>@endforeach</select></div>
                    <x-supplier-document.field name="customer_name" :label="__('Pelanggan / Syarikat')" x-model="customer.name" required maxlength="255" :value="$values['customer_name'] ?? ''" />
                </div>
                <x-supplier-document.field name="customer_address" :label="__('Alamat Pelanggan')" type="textarea" x-model="customer.address" required maxlength="2000" :value="$values['customer_address'] ?? ''" />
            </div>
        </x-common.document-card>
        @include('pages.invoice.items')
        <x-common.document-card :title="__('ARAHAN PEMBAYARAN / PAYMENT INSTRUCTIONS:')">
            <x-supplier-document.field name="payment_instructions" :label="__('Arahan Pembayaran')" type="textarea" :value="$values['payment_instructions'] ?? __('Sila lakukan pembayaran ke akaun bank korporat kami seperti tertera di bawah:')" required maxlength="3000" />
            <div class="grid gap-5 md:grid-cols-3">
                <x-supplier-document.field name="bank_name" :label="__('Nama Bank')" required :value="$values['bank_name'] ?? ''" />
                <x-supplier-document.field name="account_name" :label="__('Nama Akaun')" :value="$values['account_name'] ?? config('purchase_order.issuer_name')" required />
                <x-supplier-document.field name="account_no" :label="__('No. Akaun')" required :value="$values['account_no'] ?? ''" />
            </div>
        </x-common.document-card>
        <x-common.document-card :title="__('Disediakan oleh,')">
            <div class="grid gap-6 md:grid-cols-2"><x-supplier-document.field name="prepared_by" :label="__('Nama Penyedia (pilihan)')" :value="$values['prepared_by'] ?? ''" /><x-supplier-document.field name="prepared_role" :label="__('Jawatan / Unit')" :value="$values['prepared_role'] ?? __('Pengurus Besar')" required /></div>
            <div class="max-w-md space-y-4 pt-12">
                <div class="border-t border-dotted border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
                <x-supplier-document.field name="signature_company" :label="__('Nama Syarikat')" list="signature-company-options" :value="$values['signature_company'] ?? config('purchase_order.issuer_name')" x-bind:value="signatureCompany ?? issuer.name" @input="signatureCompany = $event.target.value" required maxlength="255" aria-describedby="signature-company-help" />
                <datalist id="signature-company-options">
                    @foreach ($companies as $company)<option value="{{ $company->nama_syarikat }}"></option>@endforeach
                </datalist>
                <p id="signature-company-help" class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Taip nama syarikat atau pilih daripada senarai.') }}</p>
            </div>
        </x-common.document-card>
        <div class="flex flex-wrap justify-end gap-3">
            @if(!$editId)
            <button type="submit" formaction="{{ ($editId ? route('invoice.customer.edit-preview', $editId) : route('invoice.customer.draft-preview')) }}" formtarget="_blank" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Pratonton PDF') }}</button>
            @endif
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ $editId ? __('Simpan Perubahan') : __('Cipta & Simpan Invois') }}</button>
        </div>
    </form>
</x-common.document-workspace>
@endsection

