@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$quotation ? __('Edit Sebut Harga Pembekal') : __('Tambah Sebut Harga Pembekal')" />

    @php
        $initialItems = $items->isNotEmpty()
            ? $items->map(fn ($item, $index) => ['id' => $index + 1, 'description' => $item->item_description, 'quantity' => $item->quantity, 'unit' => $item->unit, 'price' => $item->unit_price])->values()
            : collect([['id' => 1, 'description' => '', 'quantity' => 1, 'unit' => '', 'price' => 0]]);
        $existingPdfUrl = $quotation?->file_path ? asset('storage/' . $quotation->file_path) : '';
    @endphp

    <form method="POST" enctype="multipart/form-data" action="{{ route('sebut-harga-pembekal.save', ['id' => $quotation?->supplier_quotation_id]) }}" class="font-outfit" x-data="{
        items: @js($initialItems),
        nextId: {{ $initialItems->count() + 1 }},
        supplierId: {{ Illuminate\Support\Js::from((string) old('supplier_id', $quotation?->supplier_id ?? '')) }},
        supplierName: '',
        suppliers: @js($suppliers->map(fn ($supplier) => ['customer_id' => $supplier->customer_id, 'company_name' => $supplier->company_name])->values()),
        quotationNoSupplier: {{ Illuminate\Support\Js::from(old('quotation_no_supplier', $quotation?->quotation_no_supplier ?? '')) }},
        quotationTitle: {{ Illuminate\Support\Js::from(old('quotation_title', $quotation?->quotation_title ?? '')) }},
        personInCharge: {{ Illuminate\Support\Js::from(old('person_in_charge', $quotation?->person_in_charge ?? '')) }},
        pdfUrl: {{ Illuminate\Support\Js::from($existingPdfUrl) }},
        fileName: {{ Illuminate\Support\Js::from($quotation?->file_name ?? '') }},
        extracting: false,
        registeringSupplier: false,
        showSupplierRegistration: false,
        newSupplier: { company_name: '', customer_name: '', address: '', phone_no: '', email: '' },
        extractMessage: '',
        extractError: '',
        choosePdf(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.type !== 'application/pdf') { event.target.value = ''; this.pdfUrl = ''; this.fileName = ''; return; }
            if (this.pdfUrl && this.pdfUrl.startsWith('blob:')) URL.revokeObjectURL(this.pdfUrl);
            this.pdfUrl = URL.createObjectURL(file);
            this.fileName = file.name;
        },
        async extractPdf() {
            const file = document.getElementById('supplier_quotation_file').files[0];
            if (!file) return;
            this.extracting = true;
            this.extractMessage = '';
            this.extractError = '';
            const formData = new FormData();
            formData.append('supplier_quotation_file', file);
            try {
                const response = await fetch('{{ route('sebut-harga-pembekal.extract') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
                    body: formData,
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'PDF tidak dapat dibaca.');
                const data = result.data || {};
                if (data.supplier_id) this.supplierId = String(data.supplier_id);
                this.supplierName = result.supplier_name || '';
                this.showSupplierRegistration = false;
                if (this.supplierName && !data.supplier_id) this.newSupplier.company_name = this.supplierName;
                if (data.quotation_no_supplier) this.quotationNoSupplier = data.quotation_no_supplier;
                if (data.quotation_title) this.quotationTitle = data.quotation_title;
                if (data.person_in_charge) this.personInCharge = data.person_in_charge;
                if (Array.isArray(data.items) && data.items.length) this.items = data.items.map((item, index) => ({ ...item, id: index + 1 }));
                this.nextId = this.items.length + 1;
                this.extractMessage = result.message || 'Dokumen berjaya dibaca.';
            } catch (error) {
                this.extractError = error.message;
            } finally {
                this.extracting = false;
            }
        },
        async registerSupplier() {
            this.registeringSupplier = true;
            this.extractError = '';
            try {
                const response = await fetch('{{ route('sebut-harga-pembekal.register-supplier') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
                    body: JSON.stringify(this.newSupplier),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Pembekal tidak berjaya didaftarkan.');
                const supplier = result.supplier;
                if (!this.suppliers.some(row => String(row.customer_id) === String(supplier.customer_id))) this.suppliers.push(supplier);
                this.supplierId = String(supplier.customer_id);
                this.supplierName = supplier.company_name;
                this.showSupplierRegistration = false;
                this.extractMessage = result.message;
            } catch (error) {
                this.extractError = error.message;
            } finally {
                this.registeringSupplier = false;
            }
        },
        addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) },
        subtotal(item) { return (Number(item.quantity) || 0) * (Number(item.price) || 0) },
        get total() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) },
        money(value) { return new Intl.NumberFormat('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) }
    }">
        @csrf
        <div class="space-y-6">
            <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
            <section class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Dokumen Pembekal') }}</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Upload PDF dan baca maklumat secara automatik.') }}</p></div>
                    <div class="flex flex-wrap gap-2">
                        <label for="supplier_quotation_file" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Upload PDF') }}</label>
                        <button type="button" x-show="fileName" @click="extractPdf()" :disabled="extracting" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-wait disabled:opacity-60"><span x-text="extracting ? 'Membaca...' : 'Baca Dokumen'"></span></button>
                    </div>
                    <input id="supplier_quotation_file" name="supplier_quotation_file" type="file" accept="application/pdf" class="sr-only" @change="choosePdf($event)">
                </div>
                <div class="border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <div x-cloak x-show="fileName" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300"><svg class="h-4 w-4 text-error-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2h8l4 4v16H6V2Zm7 1.5V7h3.5L13 3.5Z"/></svg><span class="truncate" x-text="fileName"></span></div>
                    <p x-cloak x-show="extractMessage" class="mt-1 text-sm text-success-700 dark:text-success-400" x-text="extractMessage"></p>
                    <p x-cloak x-show="extractError" class="mt-1 text-sm text-error-600 dark:text-error-400" x-text="extractError"></p>
                    <p x-show="!fileName" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada PDF dipilih.') }}</p>
                </div>
                <div class="min-h-[360px] bg-gray-100 p-3 dark:bg-gray-950/40">
                    <div x-cloak x-show="pdfUrl" class="h-[360px] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700"><iframe :src="pdfUrl" title="{{ __('Preview PDF Sebut Harga Pembekal') }}" class="h-full w-full"></iframe></div>
                    <div x-show="!pdfUrl" class="flex h-[480px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-8 text-center dark:border-gray-700 dark:bg-gray-900/50"><div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h7l4 4v14H7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M8 13h8M8 17h6"/></svg></div><h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview PDF akan muncul di sini') }}</h3><p class="mt-2 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Upload sebut harga pembekal untuk melihat dokumen sebelum memasukkan item di bawah.') }}</p><label for="supplier_quotation_file" class="mt-5 cursor-pointer text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Pilih fail PDF') }}</label></div>
                </div>
            </section>

            <div class="min-w-0">
                <x-common.document-card :title="__('Maklumat Sebut Harga')" class="h-full">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="supplier_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Nama Syarikat') }}</label>
                            <select id="supplier_id" name="supplier_id" x-model="supplierId" required class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('Pilih pembekal') }}</option>
                                <template x-for="supplier in suppliers" :key="supplier.customer_id"><option :value="supplier.customer_id" x-text="supplier.company_name"></option></template>
                            </select>
                            <div x-cloak x-show="supplierName && !supplierId" class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="me-2 mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center text-warning-600 dark:text-warning-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.75 3h14.9a2 2 0 0 0 1.75-3l-7.5-13.3a2 2 0 0 0-3.4 0Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-600 dark:text-gray-300">Pembekal belum berdaftar: <span class="text-gray-700 dark:text-gray-300" x-text="supplierName"></span></p>
                                    </div>
                                </div>
                                <span aria-hidden="true" class="mx-2 text-transparent">&nbsp;</span>
                                <button type="button" @click="showSupplierRegistration = !showSupplierRegistration; newSupplier.company_name = supplierName" class="font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300" x-text="showSupplierRegistration ? 'Tutup borang' : 'Daftar sekarang'"></button>
                            </div>
                            <div x-cloak x-show="showSupplierRegistration" x-transition class="mt-3 rounded-xl border border-brand-100 bg-brand-50/40 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Daftar pembekal baharu</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maklumat ini akan disimpan dalam senarai pembekal.</p>
                                    </div>
                                    <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[11px] font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">Maklumat wajib *</span>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <label class="block"><span class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Nama syarikat <span class="text-error-500">*</span></span><input x-model="newSupplier.company_name" :required="showSupplierRegistration" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></label>
                                    <label class="block"><span class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Nama pegawai</span><input x-model="newSupplier.customer_name" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></label>
                                    <label class="block"><span class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Nombor telefon</span><input x-model="newSupplier.phone_no" type="tel" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></label>
                                    <label class="block"><span class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">E-mel</span><input x-model="newSupplier.email" type="email" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></label>
                                    <label class="block md:col-span-2"><span class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Alamat <span class="text-error-500">*</span></span><textarea x-model="newSupplier.address" :required="showSupplierRegistration" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea></label>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="button" @click="registerSupplier()" :disabled="registeringSupplier" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-wait disabled:opacity-60" x-text="registeringSupplier ? 'Menyimpan...' : 'Daftar & pilih pembekal'"></button>
                                </div>
                            </div>
                        </div>
                        <div><label for="quotation_no_supplier" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('No. Sebut Harga Pembekal') }}</label><input id="quotation_no_supplier" name="quotation_no_supplier" x-model="quotationNoSupplier" required class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                        <div><label for="quotation_title" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tajuk Sebut Harga') }}</label><input id="quotation_title" name="quotation_title" x-model="quotationTitle" class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                        <div><label for="person_in_charge" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Person In Charge') }}</label><input id="person_in_charge" name="person_in_charge" x-model="personInCharge" class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                    </div>
                </x-common.document-card>

            </div>

            </div>

            <div class="min-w-0">
                <x-common.document-card :title="__('Item Sebut Harga')">
                    <div class="space-y-4">
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full table-fixed">
                                <thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach (['Bil.', 'Perihal', 'Qty', 'Unit', 'Harga (RM)', 'Jumlah (RM)', ''] as $heading)<th class="px-3 py-3 text-start text-theme-xs font-semibold text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>@endforeach</tr></thead>
                                <tbody><template x-for="(item, index) in items" :key="item.id"><tr class="border-t border-gray-100 dark:border-gray-800"><td class="w-[5%] p-2 text-center text-sm text-gray-500" x-text="index + 1"></td><td class="w-[32%] p-2"><textarea :name="`items[${index}][description]`" x-model="item.description" rows="2" required class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea></td><td class="w-[10%] p-2"><input :name="`items[${index}][quantity]`" x-model.number="item.quantity" type="number" min="1" step="1" required class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></td><td class="w-[12%] p-2"><input :name="`items[${index}][unit]`" x-model="item.unit" required class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></td><td class="w-[16%] p-2"><input :name="`items[${index}][price]`" x-model.number="item.price" type="number" min="0" step="0.01" required class="price-input w-full rounded-lg border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></td><td class="w-[16%] whitespace-nowrap p-2 text-end text-sm font-medium" x-text="money(subtotal(item))"></td><td class="w-[9%] p-2"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-xs font-medium text-error-600 disabled:opacity-30 dark:text-error-400">{{ __('Buang') }}</button></td></tr></template></tbody>
                            </table>
                        </div>
                        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-3 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">+ {{ __('Tambah Item') }}</button>
                        <div class="flex justify-end border-t border-gray-100 pt-4 text-sm dark:border-gray-800"><span class="font-semibold text-gray-800 dark:text-white/90">{{ __('Jumlah Total') }}: RM <span x-text="money(total)"></span></span></div>
                    </div>
                </x-common.document-card>

            </div>

            <div class="flex justify-end gap-3">
                    <a href="{{ route('sebut-harga-pembekal') }}" aria-label="{{ __('Kembali ke Senarai Sebut Harga Pembekal') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        {{ __('Kembali') }}
                    </a>
                    <button class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan') }}</button>
                </div>
        </div>
    </form>
@endsection
