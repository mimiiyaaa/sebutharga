@props(['suppliers' => collect(), 'quotation', 'nextPoNo', 'order' => null, 'initialItems' => [], 'returnToDetail' => false, 'showBack' => true])

@php
    $inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<form method="POST" action="{{ $order ? route('purchase-order.update', $order->purchase_order_id) : route('purchase-order.store') }}" class="space-y-6" x-data="{
    suppliers: {{ Illuminate\Support\Js::from($suppliers) }},
    supplierId: {{ Illuminate\Support\Js::from((string) old('customer_id', $order?->customer_id ?? '')) }},
    supplier: {},
    attentionSupplier: {{ Illuminate\Support\Js::from(old('attention_supplier', $order?->attention_supplier ?? '')) }},
    delivery: {{ Illuminate\Support\Js::from(['company_name' => config('purchase_order.issuer_name'), 'address' => old('delivery_address', $order?->delivery_address ?? config('purchase_order.issuer_address')), 'phone_no' => config('purchase_order.issuer_phone'), 'attention' => old('attention_delivery', $order?->attention_delivery ?? config('purchase_order.delivery_attention'))]) }},
    init() { this.supplier = this.suppliers.find(row => String(row.customer_id) === this.supplierId) || {}; },
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
    <input type="hidden" name="quotation_id" value="{{ $quotation->quotation_id }}">
    <input type="hidden" name="quotation_detail_id" value="{{ $quotation->quotation_detail_id }}">
    <input type="hidden" name="items_json" :value="JSON.stringify(items)">
    @if ($errors->any())
        <ul role="alert" class="rounded-lg bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card :title="__('Maklumat Syarikat Pengeluar PO')">
            @foreach (['issuer_name' => 'Nama Syarikat', 'issuer_phone' => 'Nombor Telefon', 'issuer_email' => 'E-mel'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ __($label) }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, config('purchase_order.'.$field)) }}" type="{{ $field === 'issuer_email' ? 'email' : ($field === 'issuer_phone' ? 'tel' : 'text') }}" class="{{ $inputClass }}">
                </div>
            @endforeach
            <div>
                <label for="issuer_address" class="{{ $labelClass }}">{{ __('Alamat Syarikat') }}</label>
                <textarea id="issuer_address" name="issuer_address" rows="3" class="{{ $inputClass }}">{{ old('issuer_address', config('purchase_order.issuer_address')) }}</textarea>
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
<input type="hidden" name="quotation_id" value="{{ $quotation->quotation_id }}">
<input type="hidden" name="quotation_detail_id" value="{{ $quotation->quotation_detail_id }}">
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
            <label for="delivery_company" class="{{ $labelClass }}">{{ __('Nama Syarikat Penerima') }}</label>
            <input id="delivery_company" name="delivery_company" x-model="delivery.company_name" class="{{ $inputClass }}">
        </div>
        <div>
            <label for="delivery_phone" class="{{ $labelClass }}">{{ __('Nombor Telefon Penerima') }}</label>
            <input id="delivery_phone" name="delivery_phone" type="tel" x-model="delivery.phone_no" class="{{ $inputClass }}">
        </div>
        <div>
            <label for="delivery_address" class="{{ $labelClass }}">{{ __('Alamat Penghantaran') }}</label>
            <textarea id="delivery_address" name="delivery_address" rows="3" x-model="delivery.address" class="{{ $inputClass }}"></textarea>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach (['attention_delivery' => 'Untuk Perhatian Penerima'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ __($label) }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" x-model="delivery.attention" class="{{ $inputClass }}">
                </div>
            @endforeach
        </div>
    </x-common.document-card>
    </div>

    <x-common.document-card :title="__('Item PO')">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-start text-theme-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white">
                    <tr>
                        @foreach (['Bil.', 'Keterangan / Description', 'Kuantiti', 'Unit', 'Harga Seunit (RM)', 'Jumlah (RM)', 'Tindakan'] as $heading)
                            <th scope="col" class="whitespace-nowrap px-3 py-3 text-start text-theme-xs font-semibold">{{ __($heading) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="p-3 text-center tabular-nums" x-text="index + 1"></td>
                            <td class="min-w-60 p-3"><textarea rows="3" required x-model="item.description" aria-label="{{ __('Keterangan / Description') }}" placeholder="{{ __('Nama item / perkhidmatan') }}" class="{{ $inputClass }}"></textarea></td>
                            <td class="min-w-32 p-3"><input type="number" min="1" step="1" required x-model.number="item.quantity" aria-label="{{ __('Kuantiti') }}" class="{{ $inputClass }} text-center"></td>
                            <td class="min-w-32 p-3"><input required x-model="item.unit" aria-label="{{ __('Unit') }}" placeholder="{{ __('Unit') }}" class="{{ $inputClass }} text-center"></td>
                            <td class="min-w-40 p-3"><input type="number" min="0" step="0.01" required x-model.number="item.price" aria-label="{{ __('Harga Seunit (RM)') }}" class="{{ $inputClass }} price-input text-end"></td>
                            <td class="whitespace-nowrap p-3" x-text="money(subtotal(item))"></td>
                            <td class="p-3"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-error-400">{{ __('Buang') }}</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
        $additionalTerms = implode("\n", array_slice(preg_split('/\r?\n/', $storedTerms), 4));
    @endphp
    <x-common.document-card :title="__('Terma dan Syarat Pembelian')" x-data="{ periods: { delivery: {{ old('delivery_days', $deliveryMatch[1] ?? 14) }}, payment: {{ old('payment_days', $paymentMatch[1] ?? 30) }} }, insertNextTerm(event) { const field = event.target; const before = field.value.slice(0, field.selectionStart); const after = field.value.slice(field.selectionEnd); const nextNumber = 5 + before.split(/\r?\n/).filter(Boolean).length; const insertion = `\n${nextNumber}. `; field.value = before + insertion + after; field.dispatchEvent(new Event('input', { bubbles: true })); requestAnimationFrame(() => { const cursor = before.length + insertion.length; field.setSelectionRange(cursor, cursor); }); } }">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div><label for="delivery_days" class="{{ $labelClass }}">{{ __('Tempoh Penghantaran (Hari)') }}</label><input id="delivery_days" name="delivery_days" type="number" min="1" max="3650" required x-model="periods.delivery" class="{{ $inputClass }}"></div>
            <div><label for="payment_days" class="{{ $labelClass }}">{{ __('Tempoh Pembayaran (Hari)') }}</label><input id="payment_days" name="payment_days" type="number" min="1" max="3650" required x-model="periods.payment" class="{{ $inputClass }}"></div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Tempoh penghantaran dan pembayaran dikira dari tarikh PO dikeluarkan serta pengesahan penerimaan dokumen.') }}</p>
        <div class="space-y-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
            <p>1. {{ __('Sila nyatakan nombor Purchase Order (PO) ini di dalam Invois dan Nota Serahan (DO) rasmi anda.') }}</p>
            <p>2. {{ __('Penghantaran mestilah menepati spesifikasi teknikal yang dipesan. Barangan rosak/salah akan dipulangkan.') }}</p>
            <p>3. {{ __('Sila hantar bekalan dalam tempoh') }} <span class="font-semibold" x-text="periods.delivery || '—'"></span> {{ __('hari bekerja dari tarikh dokumen ini dikeluarkan.') }}</p>
            <p>4. {{ __('Pembayaran penuh akan diproses dalam tempoh') }} <span class="font-semibold" x-text="periods.payment || '—'"></span> {{ __('hari (Kredit) selepas pengesahan penerimaan DO & Invois.') }}</p>
        </div>
        <div><label for="additional_terms" class="{{ $labelClass }}">{{ __('Terma Tambahan') }}</label><textarea id="additional_terms" name="additional_terms" rows="4" maxlength="10000" @keydown.enter.prevent="insertNextTerm($event)" class="{{ $inputClass }}">{{ old('additional_terms', $additionalTerms) }}</textarea><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Tekan Enter untuk sambung nombor terma secara automatik bermula daripada 5.') }}</p></div>
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
