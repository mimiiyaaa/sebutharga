@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$title">
    <x-slot:referenceActions><a href="{{ route('invoice.customer.create') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
    <form method="POST" action="{{ route('invoice.customer.store') }}" class="space-y-6 rounded-3xl bg-brand-50/35 p-3 dark:bg-brand-500/5 sm:p-4" x-data="{
        companies: @js($companies), customers: @js($customers),
        issuer: @js(['name'=>old('issuer_name', config('purchase_order.issuer_name')), 'address'=>old('issuer_address', config('purchase_order.issuer_address')), 'phone'=>old('issuer_phone', config('purchase_order.issuer_phone')), 'email'=>old('issuer_email', config('purchase_order.issuer_email'))]),
        customer: @js(['name'=>old('customer_name', ''), 'address'=>old('customer_address', '')]),
        signatureCompany: @js(old('signature_company')),
        items: @js(collect(old('items', [['description'=>'', 'quantity'=>1, 'unit'=>'Unit', 'price'=>0]]))->values()->map(fn($item,$index)=>array_merge($item,['id'=>$index+1]))),
        nextId: {{ count(old('items', [1])) + 1 }},
        selectCompany(id) { const c = this.companies.find(c => String(c.company_id) === id); if(c) this.issuer = {name:c.nama_syarikat, address:c.alamat_syarikat || '', phone:c.no_telefon || '', email:c.emel || ''}; },
        selectCustomer(id) { const c = this.customers.find(c => String(c.customer_id) === id); if(c) this.customer = {name:c.company_name || c.customer_name, address:c.address || ''}; },
        addItem() { this.items.push({id:this.nextId++, description:'', quantity:1, unit:'Unit', price:0}); },
        subtotal(item) { return Math.round(Math.max(0, Number(item.quantity)||0) * Math.max(0, Number(item.price)||0)*100)/100; },
        get gross() { return this.items.reduce((sum,item)=>sum+this.subtotal(item),0); },
        money(value) { return new Intl.NumberFormat('en-MY',{style:'currency',currency:'MYR'}).format(value); }
    }">
        @csrf
        @if ($errors->any())
            <ul role="alert" class="rounded-xl bg-error-50 p-5 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        <div class="grid gap-6 xl:grid-cols-2">
            <x-common.document-card :title="__('Maklumat Syarikat')" :desc="__('Kepala surat di bahagian atas invois.')">
                <div><label for="company_id" class="mb-1.5 block text-sm">{{ __('Pilih syarikat') }}</label><select id="company_id" @change="selectCompany($event.target.value)" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 dark:border-gray-700 dark:bg-gray-900"><option value="">{{ __('Pilih syarikat') }}</option>@foreach($companies as $company)<option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>@endforeach</select></div>
                <x-supplier-document.field name="issuer_name" :label="__('Nama Syarikat')" x-model="issuer.name" required maxlength="255" />
                <x-supplier-document.field name="issuer_address" :label="__('Alamat Syarikat')" type="textarea" x-model="issuer.address" required maxlength="2000" />
                <div class="grid gap-5 sm:grid-cols-2"><x-supplier-document.field name="issuer_phone" :label="__('Nombor Telefon')" x-model="issuer.phone" type="tel" /><x-supplier-document.field name="issuer_email" :label="__('E-mel')" x-model="issuer.email" type="email" /></div>
            </x-common.document-card>
            <x-common.document-card :title="__('Maklumat Invois')">
                <x-supplier-document.field name="invoice_no" :label="__('No. Dokumen')" :value="$nextNumber" required maxlength="100" pattern="[A-Za-z0-9._\-]+" />
                <x-supplier-document.field name="invoice_date" :label="__('Tarikh Invois')" type="date" :value="now()->format('Y-m-d')" required />
                <p class="rounded-xl bg-gray-50 p-4 text-sm leading-6 text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ __('Invois dijana dalam format A4 dengan kepala surat, butiran bil, item, arahan pembayaran dan tandatangan.') }}</p>
            </x-common.document-card>
        </div>
        <x-common.document-card :title="__('Kepada / Bill To:')">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-5">
                    <div><label for="customer_id" class="mb-1.5 block text-sm">{{ __('Pilih Pelanggan') }}</label><select id="customer_id" name="customer_id" required @change="selectCustomer($event.target.value)" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 dark:border-gray-700 dark:bg-gray-900"><option value="">{{ __('Pilih Pelanggan') }}</option>@foreach($customers as $customer)<option value="{{ $customer->customer_id }}" @selected((string)old('customer_id') === (string)$customer->customer_id)>{{ $customer->company_name ?: $customer->customer_name }}</option>@endforeach</select></div>
                    <x-supplier-document.field name="customer_name" :label="__('Pelanggan / Syarikat')" x-model="customer.name" required maxlength="255" />
                </div>
                <x-supplier-document.field name="customer_address" :label="__('Alamat Pelanggan')" type="textarea" x-model="customer.address" required maxlength="2000" />
            </div>
        </x-common.document-card>
        @include('pages.invoice.items')
        <x-common.document-card :title="__('ARAHAN PEMBAYARAN / PAYMENT INSTRUCTIONS:')">
            <x-supplier-document.field name="payment_instructions" :label="__('Arahan Pembayaran')" type="textarea" :value="__('Sila lakukan pembayaran ke akaun bank korporat kami seperti tertera di bawah:')" required maxlength="3000" />
            <div class="grid gap-5 md:grid-cols-3">
                <x-supplier-document.field name="bank_name" :label="__('Nama Bank')" required />
                <x-supplier-document.field name="account_name" :label="__('Nama Akaun')" :value="config('purchase_order.issuer_name')" required />
                <x-supplier-document.field name="account_no" :label="__('No. Akaun')" required />
            </div>
        </x-common.document-card>
        <x-common.document-card :title="__('Disediakan oleh,')">
            <div class="grid gap-6 md:grid-cols-2"><x-supplier-document.field name="prepared_by" :label="__('Nama Penyedia (pilihan)')" /><x-supplier-document.field name="prepared_role" :label="__('Jawatan / Unit')" :value="__('Pengurus Besar')" required /></div>
            <div class="max-w-md space-y-4 pt-12">
                <div class="border-t border-dotted border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
                <x-supplier-document.field name="signature_company" :label="__('Nama Syarikat')" list="signature-company-options" :value="config('purchase_order.issuer_name')" x-bind:value="signatureCompany ?? issuer.name" @input="signatureCompany = $event.target.value" required maxlength="255" aria-describedby="signature-company-help" />
                <datalist id="signature-company-options">
                    @foreach ($companies as $company)<option value="{{ $company->nama_syarikat }}"></option>@endforeach
                </datalist>
                <p id="signature-company-help" class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Taip nama syarikat atau pilih daripada senarai.') }}</p>
            </div>
        </x-common.document-card>
        <div class="flex flex-wrap justify-end gap-3">
            <button type="submit" formaction="{{ route('invoice.customer.draft-preview') }}" formtarget="_blank" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Pratonton PDF') }}</button>
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Cipta & Simpan Invois') }}</button>
        </div>
    </form>
</x-common.document-workspace>
@endsection

