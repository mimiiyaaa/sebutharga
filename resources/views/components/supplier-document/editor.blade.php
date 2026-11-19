@props(['documentType', 'documentNumberLabel', 'invoice' => false, 'supplier' => false])
@php
    $inputClass = 'min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $sections = [
        __('Maklumat Syarikat') => [
            ['issuer_name', __('Nama Syarikat')], ['issuer_phone', __('Nombor Telefon'), 'tel'],
            ['issuer_email', __('E-mel'), 'email'], ['issuer_person_in_charge', __('Person In Charge')], ['issuer_address', __('Alamat Syarikat'), 'textarea'],
        ],
        __($documentType) => [
            ['document_no', __($documentNumberLabel)], ['document_date', __('Tarikh Dokumen'), 'date', now()->format('Y-m-d')],
            [$invoice ? 'due_date' : 'delivery_date', $invoice ? __('Tarikh Akhir Bayaran') : __('Tarikh Penghantaran'), 'date'],
            ['quotation_reference', __('No. Rujukan Sebut Harga')], ['po_reference', __('No. PO')], ['related_reference', $invoice ? __('No. DO') : __('No. Invois')],
        ],
        $supplier ? __('Maklumat Pembekal') : __('Maklumat Pelanggan') => [
            [$supplier ? 'supplier_name' : 'customer_name', $supplier ? __('Nama Pembekal') : __('Pelanggan / Syarikat')],
            ['party_phone', __('Nombor Telefon'), 'tel'], ['party_email', __('E-mel'), 'email'], ['party_attention', __('Person In Charge')],
            ['party_address', $supplier ? __('Alamat Pembekal') : __('Alamat Pelanggan'), 'textarea'],
        ],
        __('Maklumat Penghantaran') => [
            ['delivery_company', __('Nama Syarikat / Penerima')], ['delivery_attention', __('Person In Charge')],
            ['delivery_phone', __('Nombor Telefon'), 'tel'], ['delivery_method', __('Kaedah Penghantaran')], ['delivery_address', __('Alamat Penghantaran'), 'textarea'],
        ],
    ];
@endphp
<div class="space-y-6 rounded-3xl bg-brand-50/35 p-3 dark:bg-brand-500/5 sm:p-4" x-data="{
    items: [{ id: 1, description: '', quantity: 1, unit: '', price: 0 }], nextId: 2, discount: 0, sst: 0,
    addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }); },
    subtotal(item) { return Math.round(Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.price) || 0) * 100) / 100; },
    get gross() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0); },
    get discountAmount() { return Math.round(this.gross * Math.min(100, Math.max(0, Number(this.discount) || 0))) / 100; },
    get sstAmount() { return Math.round((this.gross - this.discountAmount) * Math.min(100, Math.max(0, Number(this.sst) || 0))) / 100; },
    get net() { return this.gross - this.discountAmount + this.sstAmount; },
    get quantity() { return this.items.reduce((sum, item) => sum + Math.max(0, Number(item.quantity) || 0), 0); },
    money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(value); }
}">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @foreach ($sections as $heading => $fields)
            <x-common.document-card :title="$heading">
                @foreach ($fields as $field)
                    <x-supplier-document.field :name="$field[0]" :label="$field[1]" :type="$field[2] ?? 'text'" :value="$field[3] ?? ''" />
                @endforeach
            </x-common.document-card>
        @endforeach
    </div>
    <x-supplier-document.items :invoice="$invoice" />
    <x-common.document-card :title="$invoice ? __('Maklumat Pembayaran dan Terma') : __('Catatan Penghantaran')">
        @if ($invoice)
            <div class="grid gap-5 md:grid-cols-3">
                <x-supplier-document.field name="bank_name" :label="__('Nama Bank')" />
                <x-supplier-document.field name="account_name" :label="__('Nama Pemegang Akaun')" />
                <x-supplier-document.field name="account_no" :label="__('No. Akaun Bank')" />
            </div>
            <x-supplier-document.field name="payment_terms" :label="__('Terma Pembayaran')" type="textarea" />
        @endif
        <x-supplier-document.field name="notes" :label="__('Catatan / Arahan Tambahan')" type="textarea" />
    </x-common.document-card>
    <x-common.document-card :title="$invoice ? __('Pengesahan Invois') : __('Pengesahan Penghantaran dan Penerimaan')">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach (['prepared' => __('Disediakan Oleh'), 'verified' => ($invoice ? __('Disahkan Oleh') : __('Diterima Oleh'))] as $role => $label)
                <div class="space-y-5 rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                    <x-supplier-document.field :name="$role . '_by'" :label="$label" :value="$role === 'prepared' ? auth()->user()?->name : ''" />
                    <x-supplier-document.field :name="$role . '_role'" :label="__('Jawatan / Unit')" />
                    <x-supplier-document.field :name="$role . '_date'" :label="__('Tarikh')" type="date" />
                    <div class="h-16" aria-hidden="true"></div>
                    <div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
                </div>
            @endforeach
        </div>
    </x-common.document-card>
    <div class="flex flex-col items-end gap-2">
        <button type="button" disabled aria-describedby="document-save-status" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-500 dark:text-white">
            <svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2ZM7 3v6h10V3M7 21v-8h10v8" /></svg>
            {{ $invoice ? __('Simpan Invois') : __('Simpan DO') }}
        </button>
        <p id="document-save-status" class="text-end text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Fungsi simpan belum tersedia.') }}</p>
    </div>
</div>
