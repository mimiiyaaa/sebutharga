@props(['suppliers' => collect(), 'companies' => collect(), 'quotation', 'nextPoNo', 'order' => null, 'initialItems' => [], 'returnToDetail' => false, 'showBack' => true])

@php
    $inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $documentData = $order?->document_data ? (json_decode($order->document_data, true) ?: []) : [];
    $savedIssuerCompanyId = $companies->firstWhere('nama_syarikat', $documentData['issuer_name'] ?? null)?->company_id ?? '';
    $defaultIssuerCompanyId = $companies->firstWhere('nama_syarikat', config('purchase_order.issuer_name'))?->company_id ?? '';
    $savedDeliveryCompanyId = $companies->firstWhere('nama_syarikat', $documentData['delivery_company'] ?? null)?->company_id ?? '';
@endphp

<form method="POST" action="{{ $order ? route('purchase-order.update', $order->purchase_order_id) : route('purchase-order.store') }}" class="space-y-6 rounded-3xl bg-brand-50/35 p-3 dark:bg-brand-500/5 sm:p-4" x-data="{
    suppliers: {{ Illuminate\Support\Js::from($suppliers) }},
    supplierId: {{ Illuminate\Support\Js::from((string) old('customer_id', $order?->customer_id ?? ($quotation->customer_id ?? ''))) }},
    supplier: {},
    companies: {{ Illuminate\Support\Js::from($companies) }},
    companyId: {{ Illuminate\Support\Js::from((string) old('company_id', $savedIssuerCompanyId ?: $defaultIssuerCompanyId)) }},
    deliveryRecipientType: {{ Illuminate\Support\Js::from(old('delivery_recipient_type', $savedDeliveryCompanyId !== '' ? 'company' : '')) }},
    deliveryCompanyId: {{ Illuminate\Support\Js::from((string) old('delivery_company_id', $savedDeliveryCompanyId)) }},
    deliveryCustomerId: {{ Illuminate\Support\Js::from((string) old('delivery_customer_id', '')) }},
    issuer: {
        name: {{ Illuminate\Support\Js::from(old('issuer_name', $documentData['issuer_name'] ?? '')) }},
        phone: {{ Illuminate\Support\Js::from(old('issuer_phone', $documentData['issuer_phone'] ?? '')) }},
        email: {{ Illuminate\Support\Js::from(old('issuer_email', $documentData['issuer_email'] ?? '')) }},
        personInCharge: {{ Illuminate\Support\Js::from(old('issuer_person_in_charge', $documentData['issuer_person_in_charge'] ?? '')) }},
        address: {{ Illuminate\Support\Js::from(old('issuer_address', $documentData['issuer_address'] ?? '')) }},
    },
    attentionSupplier: {{ Illuminate\Support\Js::from(old('attention_supplier', $order?->attention_supplier ?? '')) }},
    delivery: {{ Illuminate\Support\Js::from(['company_name' => old('delivery_company', $documentData['delivery_company'] ?? ''), 'address' => old('delivery_address', $order?->delivery_address ?? ''), 'phone_no' => old('delivery_phone', $documentData['delivery_phone'] ?? ''), 'attention' => old('attention_delivery', $order?->attention_delivery ?? '')]) }},
    init() {
        this.supplier = this.suppliers.find(row => String(row.customer_id) === this.supplierId) || {};
        if (this.companyId && !this.issuer.name) this.selectCompany();
    },
    get selectedCompany() { return this.companies.find(row => String(row.company_id) === String(this.companyId)); },
    selectCompany() {
        const company = this.selectedCompany;
        if (!company) return;
        this.issuer.name = company.nama_syarikat || '';
        this.issuer.phone = company.no_telefon || '';
        this.issuer.email = company.emel || '';
        this.issuer.personInCharge = company.person_in_charge || '';
        this.issuer.address = company.alamat_syarikat || '';
    },
    selectDeliveryCompany() {
        const company = this.companies.find(row => String(row.company_id) === String(this.deliveryCompanyId));
        if (!company) return;
        this.delivery.company_name = company.nama_syarikat || '';
        this.delivery.phone_no = company.no_telefon || '';
        this.delivery.address = company.alamat_syarikat || '';
    },
    selectDeliveryCustomer() {
        const customer = this.suppliers.find(row => String(row.customer_id) === String(this.deliveryCustomerId));
        if (!customer) return;
        this.delivery.company_name = customer.company_name || customer.customer_name || '';
        this.delivery.phone_no = customer.phone_no || '';
        this.delivery.address = customer.address || '';
    },
    selectSupplier() {
        this.supplier = this.suppliers.find(row => String(row.customer_id) === String(this.supplierId)) || {};
        this.attentionSupplier = this.supplier.customer_name || '';
    },
    nextId: {{ count($initialItems) + 2 }},
    items: {{ Illuminate\Support\Js::from($initialItems ?: [['id'=>1, 'description'=>'', 'quantity'=>1, 'unit'=>'', 'price'=>0]]) }},
    discount: {{ Illuminate\Support\Js::from(old('discount_percent', $order?->discount_percent ?? 0)) }},
    sst: {{ Illuminate\Support\Js::from(old('sst_percent', $order?->sst_percent ?? 0)) }},
    subtotal(item) { return Math.round(Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.price) || 0) * 100) / 100 },
    get gross() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) },
    get discountAmount() { return Math.round(this.gross * Math.min(100, Math.max(0, Number(this.discount) || 0))) / 100 },
    get sstAmount() { return Math.round((this.gross - this.discountAmount) * Math.min(100, Math.max(0, Number(this.sst) || 0))) / 100 },
    get net() { return this.gross - this.discountAmount + this.sstAmount },
    money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(value) },
    addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) }
}">
    @csrf
    @if ($showBack)
        <div class="flex justify-end"><a href="{{ $order && $returnToDetail ? route('purchase-order.show', $order->purchase_order_id) : route('purchase-order.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></div>
    @endif
    @if ($order) @method('PUT') @endif
    @if ($order && $returnToDetail)<input type="hidden" name="from" value="show">@endif
    <input type="hidden" name="quotation_id" value="{{ $quotation->quotation_id ?? '' }}">
    <input type="hidden" name="quotation_detail_id" value="{{ $quotation->quotation_detail_id ?? '' }}">
    <input type="hidden" name="supplier_quotation_id" value="{{ $quotation->supplier_quotation_id ?? '' }}">
    <input type="hidden" name="items_json" :value="JSON.stringify(items)">
    @if ($errors->any())
        <ul role="alert" class="rounded-lg bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card :title="__('Maklumat Syarikat')">
            <div>
                <label for="company_id" class="{{ $labelClass }}">{{ __('Nama Syarikat') }}</label>
                <select id="company_id" name="company_id" x-model="companyId" @change="selectCompany()" class="{{ $inputClass }}">
                    <option value="">{{ __('Pilih syarikat') }}</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="issuer_name" x-model="issuer.name">
            </div>
            <div>
                <label for="issuer_phone" class="{{ $labelClass }}">{{ __('Nombor Telefon') }}</label>
                <input id="issuer_phone" name="issuer_phone" x-model="issuer.phone" type="tel" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="issuer_email" class="{{ $labelClass }}">{{ __('E-mel') }}</label>
                <input id="issuer_email" name="issuer_email" x-model="issuer.email" type="email" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="issuer_person_in_charge" class="{{ $labelClass }}">{{ __('Person In Charge') }}</label>
                <input id="issuer_person_in_charge" name="issuer_person_in_charge" x-model="issuer.personInCharge" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="issuer_address" class="{{ $labelClass }}">{{ __('Alamat Syarikat') }}</label>
                <textarea id="issuer_address" name="issuer_address" rows="3" x-model="issuer.address" class="{{ $inputClass }} h-auto"></textarea>
            </div>
        </x-common.document-card>
        <x-common.document-card :title="__('Maklumat PO')">
            <div>
                <label for="po_no" class="{{ $labelClass }}">{{ __('No. PO') }}</label>
                <input id="po_no" readonly value="{{ $nextPoNo }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="po_date" class="{{ $labelClass }}">{{ __('Tarikh PO') }}</label>
                <input id="po_date" name="po_date" type="date" value="{{ old('po_date', $order?->po_date ?? now()->format('Y-m-d')) }}" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="lo_inden_id" class="{{ $labelClass }}">{{ __('LO / Inden (pilihan)') }}</label>
                <select id="lo_inden_id" disabled class="{{ $inputClass }}">
                    <option>{{ __('Pilihan LO / Inden belum tersedia') }}</option>
                </select>
            </div>
        </x-common.document-card>

    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card :title="__('Kepada: Pembekal')">
            <div>
                <label for="customer_id" class="{{ $labelClass }}">{{ __('Pilih Pembekal') }}</label>
                <select id="customer_id" name="customer_id" x-model="supplierId" @change="selectSupplier()" required class="{{ $inputClass }}">
                    <option value="">{{ __('Sila pilih pembekal') }}</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->customer_id }}">{{ $supplier->company_name }}</option>
                    @endforeach
                </select>
                @if ($suppliers->isEmpty())
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Tiada pembekal berdaftar.') }}</p>
                @endif
            </div>
            @foreach (['attention_supplier' => 'Untuk Perhatian Pembekal', 'company_name' => 'Nama Pembekal / Syarikat', 'phone_no' => 'Nombor Telefon', 'email' => 'E-mel', 'reference_no' => 'No. Rujukan Pembekal'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ __($label) }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'phone_no' ? 'tel' : 'text') }}"
                        @if ($field !== 'attention_supplier') readonly :value="supplier.{{ $field }} || ''" @else x-model="attentionSupplier" @endif
                        class="{{ $inputClass }}">
                </div>
            @endforeach
            <div>
                <label for="supplier_address" class="{{ $labelClass }}">{{ __('Alamat Pembekal') }}</label>
                <textarea id="supplier_address" name="supplier_address" rows="3" readonly :value="supplier.address || ''" class="{{ $inputClass }}"></textarea>
            </div>
        </x-common.document-card>
    <x-common.document-card :title="__('Maklumat Penghantaran')">
        <div>
            <span class="{{ $labelClass }}">{{ __('Hantar kepada') }}</span>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400">
                    <input type="radio" name="delivery_recipient_type" value="company" x-model="deliveryRecipientType" class="peer h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="peer-checked:font-semibold peer-checked:text-brand-600 dark:peer-checked:text-brand-400">{{ __('Syarikat') }}</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400">
                    <input type="radio" name="delivery_recipient_type" value="customer" x-model="deliveryRecipientType" class="peer h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="peer-checked:font-semibold peer-checked:text-brand-600 dark:peer-checked:text-brand-400">{{ __('Pelanggan') }}</span>
                </label>
            </div>
        </div>
        <div x-show="deliveryRecipientType === 'company'" x-cloak>
            <label for="delivery_company_id" class="{{ $labelClass }}">{{ __('Pilih syarikat') }}</label>
            <select id="delivery_company_id" name="delivery_company_id" x-model="deliveryCompanyId" @change="selectDeliveryCompany()" :required="deliveryRecipientType === 'company'" class="{{ $inputClass }}">
                <option value="">{{ __('Pilih syarikat') }}</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>
                @endforeach
            </select>
        </div>
        <div x-show="deliveryRecipientType === 'customer'" x-cloak>
            <label for="delivery_customer_id" class="{{ $labelClass }}">{{ __('Pilih pelanggan') }}</label>
            <select id="delivery_customer_id" name="delivery_customer_id" x-model="deliveryCustomerId" @change="selectDeliveryCustomer()" :required="deliveryRecipientType === 'customer'" class="{{ $inputClass }}">
                <option value="">{{ __('Pilih pelanggan') }}</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->customer_id }}">{{ $supplier->company_name }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" id="delivery_company" name="delivery_company" x-model="delivery.company_name">
        <div>
            <label for="delivery_phone" class="{{ $labelClass }}">{{ __('Nombor Telefon') }}</label>
            <input id="delivery_phone" name="delivery_phone" type="tel" x-model="delivery.phone_no" class="{{ $inputClass }}">
        </div>
        <div>
            <label for="delivery_address" class="{{ $labelClass }}">{{ __('Alamat Penghantaran') }}</label>
            <textarea id="delivery_address" name="delivery_address" rows="3" x-model="delivery.address" class="{{ $inputClass }}"></textarea>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach (['attention_delivery' => 'Untuk Perhatian Penghantar'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ __('Untuk Perhatian') }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" x-model="delivery.attention" class="{{ $inputClass }}">
                </div>
            @endforeach
        </div>
    </x-common.document-card>
    </div>

    <x-common.document-card :title="__('Item PO')">
        <div class="overflow-x-auto">
            <div class="item-editor-grid grid w-full grid-cols-12 overflow-hidden rounded-xl text-theme-sm text-gray-700 dark:text-gray-300">
                <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-center text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Bil.') }}</div>
                <div class="col-span-4 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Keterangan / Description') }}</div>
                <div class="col-span-2 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Kuantiti') }}</div>
                <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Unit') }}</div>
                <div class="col-span-2 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Harga Seunit (RM)') }}</div>
                <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Jumlah (RM)') }}</div>
                <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-center text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Tindakan') }}</div>
                <template x-for="(item, index) in items" :key="item.id">
                    <div class="col-span-12 grid grid-cols-12 items-center border-b border-gray-100 py-3 dark:border-gray-800">
                        <div class="col-span-1 px-1 text-center tabular-nums" x-text="index + 1"></div>
                        <div class="col-span-4 px-1"><textarea rows="4" required x-model="item.description" aria-label="{{ __('Keterangan / Description') }}" placeholder="{{ __('Nama item / perkhidmatan') }}" class="{{ $inputClass }} w-full max-w-full"></textarea></div>
                        <div class="col-span-2 px-1"><input type="number" min="1" step="1" required x-model.number="item.quantity" aria-label="{{ __('Kuantiti') }}" class="{{ $inputClass }} w-full text-center"></div>
                        <div class="col-span-1 px-1"><input required x-model="item.unit" aria-label="{{ __('Unit') }}" placeholder="{{ __('Unit') }}" class="{{ $inputClass }} w-full text-center"></div>
                        <div class="col-span-2 px-1"><input type="number" min="0" step="0.01" required x-model.number="item.price" aria-label="{{ __('Harga Seunit (RM)') }}" class="{{ $inputClass }} w-full price-input text-end"></div>
                        <div class="col-span-1 whitespace-nowrap px-1" x-text="money(subtotal(item))"></div>
                        <div class="col-span-1 px-1 text-center"><button type="button" title="{{ __('Buang') }}" aria-label="{{ __('Buang') }}" @click="items.splice(index, 1)" :disabled="items.length === 1" class="document-action document-action-delete disabled:cursor-not-allowed disabled:opacity-40"><x-svg.item-trash /></button></div>
                    </div>
                </template>
            </div>
        </div>
        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('+ Tambah Item') }}</button>
        <div class="ms-auto max-w-sm space-y-4 text-sm text-gray-700 dark:text-gray-300" aria-live="polite">
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah Kasar') }}</span><span x-text="money(gross)"></span></div>
            <div class="flex items-center justify-between gap-4">
                <label for="discount_percent">{{ __('Diskaun (%)') }}</label>
                <div class="w-32"><input id="discount_percent" name="discount_percent" type="number" min="0" max="100" step="0.01" x-model.number="discount" class="{{ $inputClass }}"></div>
            </div>
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah Diskaun') }}</span><span x-text="money(discountAmount)"></span></div>
            <div class="flex items-center justify-between gap-4">
                <label for="sst_percent">{{ __('SST (%)') }}</label>
                <div class="w-32"><input id="sst_percent" name="sst_percent" type="number" min="0" max="100" step="0.01" x-model.number="sst" class="{{ $inputClass }}"></div>
            </div>
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah SST') }}</span><span x-text="money(sstAmount)"></span></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-lg font-semibold dark:border-gray-700"><span>{{ __('Jumlah Bersih') }}</span><span x-text="money(net)"></span></div>
        </div>
    </x-common.document-card>

    @php
        $storedTerms = old('terms_conditions', $order?->terms_conditions ?? config('purchase_order.terms'));
        preg_match('/tempoh\s+(\d+)\s+hari bekerja/i', $storedTerms, $deliveryMatch);
        preg_match('/tempoh\s+(\d+)\s+hari\s+\(Kredit\)/i', $storedTerms, $paymentMatch);
        $termLines = collect(preg_split('/\R/u', trim($storedTerms)) ?: [])
            ->map(fn ($line) => preg_replace('/^\s*\d+\.\s*/', '', trim($line)))
            ->filter(fn ($line) => $line !== '')
            ->values();
        if ($termLines->isEmpty() || ! preg_match('/^\s*1\.\s*/', trim($storedTerms))) {
            $termLines = collect([
                'Sila nyatakan nombor Purchase Order (PO) ini di dalam Invois dan Nota Serahan (DO) rasmi anda.',
                'Penghantaran mestilah menepati spesifikasi teknikal yang dipesan. Barangan rosak/salah akan dipulangkan.',
                'Sila hantar bekalan dalam tempoh ' . ($deliveryMatch[1] ?? 14) . ' hari bekerja dari tarikh dokumen ini dikeluarkan.',
                'Pembayaran penuh akan diproses dalam tempoh ' . ($paymentMatch[1] ?? 30) . ' hari (Kredit) selepas pengesahan penerimaan DO & Invois.',
            ])->merge($termLines);
        }
        $termsText = $termLines->map(fn ($line, $index) => ($index + 1) . '. ' . $line)->implode("\n") . "\n" . ($termLines->count() + 1) . '. ';
    @endphp
    <x-common.document-card :title="__('Terma dan Syarat Pembelian')" x-data="{ periods: { delivery: {{ old('delivery_days', $deliveryMatch[1] ?? 14) }}, payment: {{ old('payment_days', $paymentMatch[1] ?? 30) }} }, termsText: {{ Illuminate\Support\Js::from($termsText) }}, buildTerms() { const lines = this.termsText.split(/\r?\n/); const extras = lines.slice(4).map(line => line.replace(/^\s*\d+\.\s*/, '').trim()).filter(Boolean); return `1. Sila nyatakan nombor Purchase Order (PO) ini di dalam Invois dan Nota Serahan (DO) rasmi anda.\n2. Penghantaran mestilah menepati spesifikasi teknikal yang dipesan. Barangan rosak/salah akan dipulangkan.\n3. Sila hantar bekalan dalam tempoh ${this.periods.delivery || '—'} hari bekerja dari tarikh dokumen ini dikeluarkan.\n4. Pembayaran penuh akan diproses dalam tempoh ${this.periods.payment || '—'} hari (Kredit) selepas pengesahan penerimaan DO & Invois.` + extras.map((line, index) => `\n${index + 5}. ${line}`).join('') + `\n${extras.length + 5}. `; }, syncTerms() { this.termsText = this.buildTerms(); }, insertNextTerm(event) { const field = event.target; const before = field.value.slice(0, field.selectionStart); const after = field.value.slice(field.selectionEnd); const nextNumber = before.split(/\r?\n/).filter(Boolean).length + 1; const insertion = `\n${nextNumber}. `; field.value = before + insertion + after; field.dispatchEvent(new Event('input', { bubbles: true })); requestAnimationFrame(() => { const cursor = before.length + insertion.length; field.setSelectionRange(cursor, cursor); }); } }">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div><label for="delivery_days" class="{{ $labelClass }}">{{ __('Tempoh Penghantaran (Hari)') }}</label><input id="delivery_days" name="delivery_days" type="number" min="1" max="3650" required x-model="periods.delivery" @input="syncTerms()" class="{{ $inputClass }}"></div>
            <div><label for="payment_days" class="{{ $labelClass }}">{{ __('Tempoh Pembayaran (Hari)') }}</label><input id="payment_days" name="payment_days" type="number" min="1" max="3650" required x-model="periods.payment" @input="syncTerms()" class="{{ $inputClass }}"></div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Tempoh penghantaran dan pembayaran dikira dari tarikh PO dikeluarkan serta pengesahan penerimaan dokumen.') }}</p>
        <div><label for="additional_terms" class="{{ $labelClass }}">{{ __('Terma dan Syarat (boleh diubah)') }}</label><textarea id="additional_terms" name="additional_terms" x-model="termsText" rows="9" maxlength="10000" @keydown.enter.prevent="insertNextTerm($event)" class="{{ $inputClass }}"></textarea><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Semua ayat boleh diubah. Tekan Enter untuk sambung nombor terma seterusnya.') }}</p></div>
    </x-common.document-card>
    <x-common.document-card :title="__('Pengesahan PO')">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                <div class="space-y-5">
                    <div>
                        <label for="prepared_by" class="{{ $labelClass }}">{{ __('Disediakan Oleh') }}</label>
                        <input id="prepared_by" name="prepared_by" value="{{ old('prepared_by', $order?->prepared_by ?? auth()->user()?->name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="prepared_role" class="{{ $labelClass }}">{{ __('Jawatan / Unit Penyedia') }}</label>
                        <input id="prepared_role" name="prepared_role" value="{{ old('prepared_role', $order?->prepared_role ?? config('purchase_order.prepared_role')) }}" class="{{ $inputClass }}">
                    </div>
                </div>
                <div class="h-16" aria-hidden="true"></div>
                <div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
            </div>
            <div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                <div class="space-y-5">
                    <div>
                        <label for="approved_by" class="{{ $labelClass }}">{{ __('Diluluskan Oleh') }}</label>
                        <input id="approved_by" name="approved_by" value="{{ old('approved_by', $order?->approved_by) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="approved_role" class="{{ $labelClass }}">{{ __('Jawatan Pelulus') }}</label>
                        <input id="approved_role" name="approved_role" value="{{ old('approved_role', $order?->approved_role ?? config('purchase_order.approved_role')) }}" class="{{ $inputClass }}">
                    </div>
                </div>
                <div class="h-16" aria-hidden="true"></div>
                <div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
            </div>
        </div>
    </x-common.document-card>
    <div class="flex flex-wrap items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white">{{ __('Simpan PO') }}</button>
    </div>
</form>
