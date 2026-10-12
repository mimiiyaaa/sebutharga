@extends('layouts.app')
@section('content')
@php
    $inputClass='min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $initialItems=collect(old('items',[['description'=>'','quantity'=>1,'unit'=>'','price'=>0]]))->values()->map(fn($item,$i)=>$item+['id'=>$i+1])->all();
    $config=['invoice'=>$isInvoice,'suppliers'=>$suppliers,'csrf'=>csrf_token(),'extractUrl'=>route($prefix.'.extract'),'registerUrl'=>route('sebut-harga-pembekal.register-supplier'),
        'initial'=>['supplierId'=>(string)old('supplier_id',''),'documentNo'=>old('document_no',''),'documentDate'=>old('document_date',''),'items'=>$initialItems,'totalAmount'=>old('total_amount','')],
        'messages'=>['invalid'=>__('Pilih PDF, JPG atau PNG sehingga 10 MB.'),'failed'=>__('Bacaan gagal. Semak dokumen dan isi maklumat secara manual.'),'noItems'=>__('Item tidak dapat dikenal pasti dengan yakin. Sila masukkan item secara manual.'),'registrationRequired'=>__('Nama syarikat dan alamat diperlukan.')]];
@endphp
<x-common.document-workspace :title="$title">
<x-slot:referenceActions><a href="{{ route($prefix) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a></x-slot:referenceActions>
<form action="{{ route($prefix.'.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="supplierDocumentUpload(@js($config))" @input="if ($event.target.name !== 'reviewed') reviewed = false">
@csrf
@if($errors->any())<div role="alert" class="rounded-xl bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><p>{{ __('Sila pilih semula fail dokumen sebelum simpan.') }}</p></div>@endif
<div class="grid gap-6 xl:grid-cols-2">
<x-common.document-card :title="__('Dokumen Pembekal')">
    <label for="document_file" class="block text-sm font-medium">{{ __('Upload PDF atau imej') }}</label>
    <input id="document_file" name="document_file" type="file" accept="application/pdf,image/jpeg,image/png" required @change="chooseDocument($event)" :disabled="loading" class="{{ $inputClass }}">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih PDF, JPG atau PNG sehingga 10 MB.') }}</p>
    <p x-cloak x-show="loading" role="status" class="text-sm text-brand-600 dark:text-brand-400">{{ __('Sedang membaca dokumen...') }}</p>
    <p x-cloak x-show="message" x-text="message" role="status" class="rounded-lg bg-brand-50 p-3 text-sm text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"></p>
    <p x-cloak x-show="fileName" class="break-all text-sm"><span>{{ __('Fail asal:') }}</span> <span x-text="fileName"></span></p>
    <p x-cloak x-show="method" class="text-sm"><span>{{ __('Kaedah bacaan:') }}</span> <span x-text="method"></span></p>
    <div class="h-[480px] overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
        <template x-if="documentUrl && isImage"><img :src="documentUrl" alt="{{ __('Dokumen Pembekal') }}" class="h-full w-full object-contain"></template>
        <template x-if="documentUrl && !isImage"><iframe :src="documentUrl" title="{{ __('Dokumen Pembekal') }}" class="h-full w-full"></iframe></template>
        <p x-show="!documentUrl" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Preview dokumen akan muncul di sini') }}</p>
    </div>
    <details x-cloak x-show="rawText"><summary class="cursor-pointer text-sm font-medium">{{ __('Lihat teks yang dibaca') }}</summary><pre x-text="rawText" class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-gray-50 p-3 text-theme-xs dark:bg-gray-800"></pre></details>
</x-common.document-card>
<x-common.document-card :title="__('Maklumat Dokumen')">
    <div><label for="supplier_id" class="mb-1.5 block text-sm">{{ __('Nama Pembekal') }}</label><select id="supplier_id" name="supplier_id" x-model="supplierId" required :disabled="loading" class="{{ $inputClass }}"><option value="">{{ __('Pilih pembekal') }}</option><template x-for="supplier in suppliers" :key="supplier.customer_id"><option :value="supplier.customer_id" x-text="supplier.company_name"></option></template></select></div>
    <p x-cloak x-show="detectedSupplier && !supplierId" class="text-sm text-warning-600 dark:text-warning-400">{{ __('Pembekal dikesan:') }} <span x-text="detectedSupplier"></span>. {{ __('Pilih pembekal atau daftar pembekal baharu.') }}</p>
    <button type="button" @click="registerOpen=!registerOpen" class="text-sm font-medium text-brand-600 dark:text-brand-400">{{ __('Daftar pembekal baharu') }}</button>
    <div x-cloak x-show="registerOpen" class="space-y-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <x-supplier-document.field name="new_company_name" :label="__('Nama Syarikat')" x-model="newSupplier.company_name" />
        <x-supplier-document.field name="new_address" :label="__('Alamat Pembekal')" type="textarea" x-model="newSupplier.address" />
        <button type="button" @click="registerSupplier()" :disabled="registering" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white disabled:opacity-50 dark:bg-brand-500 dark:text-white">{{ __('Daftar & Pilih Pembekal') }}</button>
    </div>
    <x-supplier-document.field name="document_no" :label="$isInvoice ? __('No. Invois') : __('No. DO')" x-model="documentNo" required maxlength="100" />
    <x-supplier-document.field name="document_date" :label="__('Tarikh Dokumen')" type="date" x-model="documentDate" required />
    <x-supplier-document.field name="po_reference" :label="__('No. Rujukan PO (pilihan)')" maxlength="100" />
    <div><label for="source" class="mb-1.5 block text-sm">{{ __('Sumber Dokumen') }}</label><select id="source" name="source" required class="{{ $inputClass }}"><option value="">{{ __('Pilih sumber dokumen') }}</option>@foreach(['PDF','E-mel','WhatsApp','Surat','Serahan Tangan','Lain-lain'] as $source)<option value="{{ $source }}" @selected(old('source')===$source)>{{ __($source) }}</option>@endforeach</select></div>
    <x-supplier-document.field name="source_details" :label="__('Butiran Sumber (pengirim / rujukan)')" type="textarea" maxlength="1000" />
</x-common.document-card>
</div>
<x-common.document-card :title="__('Item Dokumen')" :desc="__('Semak item yang dibaca dan betulkan mengikut dokumen asal.')">
<div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300"><tr>@foreach(['Bil','Keterangan Barang','Kuantiti','Unit'] as $heading)<th class="p-3 text-start">{{ __($heading) }}</th>@endforeach @if($isInvoice)<th class="p-3 text-end">{{ __('Harga Seunit (RM)') }}</th><th class="p-3 text-end">{{ __('Jumlah (RM)') }}</th>@endif<th class="p-3">{{ __('Tindakan') }}</th></tr></thead><tbody>
<template x-for="(item,index) in items" :key="item.id"><tr class="align-top"><td class="p-3" x-text="index+1"></td><td class="min-w-60 p-3"><textarea x-model="item.description" :name="`items[${index}][description]`" required maxlength="2000" rows="3" aria-label="{{ __('Keterangan Barang') }}" class="{{ $inputClass }}"></textarea></td><td class="min-w-32 p-3"><input x-model.number="item.quantity" :name="`items[${index}][quantity]`" type="number" min="0.01" max="1000000" step="0.01" required aria-label="{{ __('Kuantiti') }}" class="{{ $inputClass }}"></td><td class="min-w-32 p-3"><input x-model="item.unit" :name="`items[${index}][unit]`" required maxlength="50" aria-label="{{ __('Unit') }}" class="{{ $inputClass }}"></td>
@if($isInvoice)<td class="min-w-40 p-3"><input x-model.number="item.price" :name="`items[${index}][price]`" type="number" min="0" max="1000000" step="0.01" required aria-label="{{ __('Harga Seunit (RM)') }}" class="{{ $inputClass }}"></td><td class="p-3 text-end" x-text="money(subtotal(item))"></td>@endif
<td class="p-3"><button type="button" @click="items.splice(index,1);reviewed=false" :disabled="items.length===1" class="text-error-600 disabled:opacity-40 dark:text-error-400">{{ __('Buang') }}</button></td></tr></template></tbody></table></div>
<button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2 text-sm text-brand-600 dark:border-brand-700 dark:text-brand-400">{{ __('+ Tambah Item') }}</button>
@if($isInvoice)<div class="ms-auto max-w-sm space-y-3"><p>{{ __('Jumlah Item (RM)') }}: <strong x-text="money(gross)"></strong></p><x-supplier-document.field name="total_amount" :label="__('Jumlah Akhir Dokumen (RM)')" x-model="totalAmount" type="number" min="0" max="9999999999.99" step="0.01" required /><p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Isi jumlah akhir seperti dokumen asal, termasuk cukai atau diskaun jika ada.') }}</p><button type="button" @click="totalAmount=gross.toFixed(2);reviewed=false" class="text-sm text-brand-600 dark:text-brand-400">{{ __('Gunakan jumlah item') }}</button></div>@endif
</x-common.document-card>
<x-common.document-card :title="__('Semakan Dokumen')"><x-supplier-document.field name="notes" :label="__('Catatan')" type="textarea" maxlength="3000" /><label class="flex items-start gap-3 text-sm"><input type="checkbox" name="reviewed" value="1" x-model="reviewed" required class="mt-1 h-4 w-4">{{ __('Saya telah menyemak maklumat dan item dengan dokumen asal.') }}</label></x-common.document-card>
<div class="flex justify-end"><button type="submit" :disabled="loading || registering" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white disabled:opacity-50 dark:bg-brand-500 dark:text-white">{{ __('Simpan Dokumen Pembekal') }}</button></div>
</form>
</x-common.document-workspace>
@endsection
