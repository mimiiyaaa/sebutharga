@props([
    'customers' => collect(),
    'quotation' => null,
    'draft' => null,
    'editing' => false,
    'submitAtTop' => false,
])

@php
    $inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $isEditing = $editing && $quotation && $draft;
    $formAction = $isEditing ? route('sebut-harga.draft.store', $quotation->quotation_id) : route('sebut-harga.store');
    $initialItems = $isEditing
        ? $draft->items->map(fn ($item, $index) => [
            'id' => $index + 1,
            'description' => $item->item_description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'price' => $item->unit_price,
        ])->values()
        : collect([['id' => 1, 'description' => '', 'quantity' => 1, 'unit' => '', 'price' => 0]]);
@endphp

<form method="POST" action="{{ $formAction }}" class="font-outfit space-y-6" x-data="{
    quotationDate: {{ Illuminate\Support\Js::from(old('quotation_date', $isEditing ? $quotation->quotation_date : now()->format('Y-m-d'))) }},
    sequences: {{ Illuminate\Support\Js::from((object) App\Helpers\QuotationNumber::sequences()) }},
    customerName: {{ Illuminate\Support\Js::from($isEditing ? ($quotation->customer_name ?? '') : '') }},
    customerCompany: {{ Illuminate\Support\Js::from($isEditing ? ($quotation->company_name ?? '') : '') }},
    get nextQuotationNo() {
        const year = this.quotationDate.slice(0, 4);
        return year.length === 4 ? 'K1R-QT-' + year + '-' + String((this.sequences[year] || 0) + 1).padStart(3, '0') : '';
    },
    nextId: {{ $initialItems->count() + 1 }},
    items: @js($initialItems),
    subtotal(item) { return Math.round(Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.price) || 0) * 100) / 100 },
    get gross() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) },
    money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(value) },
    addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) }
}">
    @csrf
    <input type="hidden" name="status_draft" value="Draf">

    @if ($submitAtTop)
        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600">Kemaskini</button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card title="Maklumat Syarikat Pengeluar">
            <div>
                <label class="{{ $labelClass }}">Nama Syarikat</label>
                <input id="issuer_name" name="issuer_name" value="{{ old('issuer_name', config('purchase_order.issuer_name')) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Nombor Telefon</label>
                <input id="issuer_phone" name="issuer_phone" value="{{ old('issuer_phone', config('purchase_order.issuer_phone')) }}" type="tel" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">E-mel</label>
                <input id="issuer_email" name="issuer_email" value="{{ old('issuer_email', config('purchase_order.issuer_email')) }}" type="email" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Alamat Syarikat</label>
                <textarea id="issuer_address" name="issuer_address" rows="3" class="{{ $inputClass }} h-auto">{{ old('issuer_address', config('purchase_order.issuer_address')) }}</textarea>
            </div>
        </x-common.document-card>

        <x-common.document-card title="Maklumat Sebut Harga">
            <div>
                <label for="quotation_no" class="{{ $labelClass }}">No. Sebut Harga</label>
                <input id="quotation_no" readonly value="{{ $isEditing ? $quotation->quotation_no : App\Helpers\QuotationNumber::next(old('quotation_date', now()->format('Y-m-d'))) }}" @if (! $isEditing) :value="nextQuotationNo" @endif class="{{ $inputClass }} bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            </div>
            <div>
                <label for="quotation_date" class="{{ $labelClass }}">Tarikh Sebut Harga</label>
                <input id="quotation_date" name="quotation_date" x-model="quotationDate" type="date" value="{{ $isEditing ? $quotation->quotation_date : now()->format('Y-m-d') }}" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="customer_id" class="{{ $labelClass }}">Pilih Pelanggan</label>
                <select id="customer_id" name="customer_id" required class="{{ $inputClass }}" @change="customerName = $event.target.selectedOptions[0]?.dataset.name || ''; customerCompany = $event.target.selectedOptions[0]?.dataset.company || ''; $refs.customerReference.value = $event.target.selectedOptions[0]?.dataset.code || ''">
                    <option value="">Pilih pelanggan</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}" data-code="{{ $customer->customer_code }}" data-name="{{ $customer->customer_name }}" data-company="{{ $customer->company_name }}" @selected($isEditing && $quotation->customer_id == $customer->customer_id)>
                            {{ $customer->company_name }}
                            @if ($customer->customer_name)
                                - {{ $customer->customer_name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <div x-cloak x-show="customerName || customerCompany" class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Nama Pelanggan</label>
                        <div class="flex min-h-11 items-center rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" x-text="customerName || '—'"></div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nama Syarikat</label>
                        <div class="flex min-h-11 items-center rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="customerCompany || '—'"></div>
                    </div>
                </div>
            </div>
            <div>
                <label for="quotation_title" class="{{ $labelClass }}">Tajuk Sebut Harga</label>
                <input id="quotation_title" name="quotation_title" value="{{ $isEditing ? $quotation->quotation_title : '' }}" maxlength="255" placeholder="Contoh: Pembekalan peralatan pejabat" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="no_rujukan_pelanggan" class="{{ $labelClass }}">No. Rujukan Pelanggan</label>
                <input id="no_rujukan_pelanggan" name="no_rujukan_pelanggan" x-ref="customerReference" readonly value="{{ $isEditing ? ($quotation->no_rujukan_pelanggan ?: $customers->firstWhere('customer_id', $quotation->customer_id)?->customer_code) : '' }}" maxlength="100" placeholder="Dijana automatik berdasarkan pelanggan" class="{{ $inputClass }} bg-gray-100 dark:bg-gray-800">
            </div>
            <div>
                @unless ($isEditing)
                <label for="status_quotation" class="{{ $labelClass }}">Keputusan Sebut Harga</label>
                <select id="status_quotation" name="status_quotation" class="{{ $inputClass }}">
                    <option value="">Menunggu Keputusan</option>
                    <option value="1" @selected($isEditing && (int) ($quotation->status_quotation ?? 0) === 1)>Setuju</option>
                    <option value="0" @selected($isEditing && $quotation->status_quotation !== null && (int) $quotation->status_quotation === 0)>Tidak Setuju</option>
                </select>
                @endunless
            </div>
        </x-common.document-card>

    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card title="Kepada: Pelanggan">
            <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400">
                Pelanggan dipilih melalui medan Nama Pelanggan dalam Maklumat Sebut Harga.
            </div>
            <div>
                <label class="{{ $labelClass }}">Jenis Dokumen</label>
                <input readonly value="Sebut Harga" class="{{ $inputClass }} bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="disediakan_oleh" class="{{ $labelClass }}">Disediakan Oleh</label>
                    <input id="disediakan_oleh" name="disediakan_oleh" value="{{ $isEditing ? $draft->disediakan_oleh : auth()->user()?->name }}" maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="diterima_oleh" class="{{ $labelClass }}">Diterima Oleh</label>
                    <input id="diterima_oleh" name="diterima_oleh" value="{{ $isEditing ? $draft->diterima_oleh : '' }}" maxlength="255" class="{{ $inputClass }}">
                </div>
            </div>
            <div>
                <label for="terma_syarat" class="{{ $labelClass }}">Terma dan Syarat</label>
                <textarea id="terma_syarat" name="terma_syarat" rows="5" placeholder="Masukkan terma dan syarat jika ada" class="{{ $inputClass }} h-auto">{{ $isEditing ? $draft->terma_syarat : '' }}</textarea>
            </div>
        </x-common.document-card>
        <x-common.document-card title="Maklumat Tambahan">
            <div>
                <label for="maklumat_tambahan" class="{{ $labelClass }}">Maklumat Tambahan</label>
                <textarea id="maklumat_tambahan" name="maklumat_tambahan" rows="5" class="{{ $inputClass }} h-auto" placeholder="Masukkan maklumat tambahan jika ada">{{ old('maklumat_tambahan', $isEditing ? ($draft->maklumat_tambahan ?? '') : '') }}</textarea>
            </div>
            <div>
                <label for="catatan" class="{{ $labelClass }}">Catatan</label>
                <textarea id="catatan" name="catatan" rows="3" class="{{ $inputClass }} h-auto" placeholder="Masukkan catatan atau arahan khas">{{ old('catatan', $isEditing ? ($draft->catatan ?? '') : '') }}</textarea>
            </div>
        </x-common.document-card>
    </div>

    <x-common.document-card title="Item Sebut Harga">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-start text-theme-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white">
                    <tr>
                        @foreach (['Bil.', 'Keterangan / Description', 'Kuantiti', 'Unit', 'Harga Seunit (RM)', 'Jumlah (RM)', 'Tindakan'] as $heading)
                            <th scope="col" class="whitespace-nowrap px-3 py-3 text-start text-theme-xs font-semibold">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="p-3 text-center tabular-nums" x-text="index + 1"></td>
                            <td class="min-w-60 p-3"><textarea :name="`items[${index}][description]`" x-model="item.description" rows="3" required class="{{ $inputClass }}" placeholder="Nama item / perkhidmatan"></textarea></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][quantity]`" type="number" min="1" step="1" x-model.number="item.quantity" required class="{{ $inputClass }} text-center"></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][unit]`" x-model="item.unit" required class="{{ $inputClass }} text-center" placeholder="Unit"></td>
                            <td class="min-w-40 p-3"><input :name="`items[${index}][price]`" type="number" min="0" step="0.01" x-model.number="item.price" required class="{{ $inputClass }} text-end"></td>
                            <td class="whitespace-nowrap p-3" x-text="money(subtotal(item))"></td>
                            <td class="p-3"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-error-400">Buang</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">+ Tambah Item</button>

        <div class="ms-auto w-full max-w-sm space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" aria-live="polite">
            <div class="flex justify-between gap-4"><span>Jumlah Kasar</span><span x-text="money(gross)"></span></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-lg font-semibold dark:border-gray-700"><span>Jumlah Total</span><span x-text="money(gross)"></span></div>
        </div>
    </x-common.document-card>

    <x-common.document-card title="Pengesahan Sebut Harga">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}">Disediakan Oleh</label>
                <input id="disediakan_oleh_confirmation" name="disediakan_oleh" value="{{ old('disediakan_oleh', $isEditing ? ($draft->disediakan_oleh ?? '') : '') }}" class="{{ $inputClass }}">
                <label class="{{ $labelClass }} mt-4">Jawatan / Unit Penyedia</label>
                <input id="disediakan_role" name="disediakan_role" value="{{ old('disediakan_role', $isEditing ? ($draft->disediakan_role ?? '') : '') }}" class="{{ $inputClass }}">
                <div class="mt-10 border-t border-dashed border-gray-400 pt-2 text-sm text-gray-500 dark:border-gray-600">Ruang Tandatangan</div>
            </div>
            <div>
                <label class="{{ $labelClass }}">Diterima / Disahkan Oleh</label>
                <input id="diterima_oleh_confirmation" name="diterima_oleh" value="{{ old('diterima_oleh', $isEditing ? ($draft->diterima_oleh ?? '') : '') }}" class="{{ $inputClass }}">
                <label class="{{ $labelClass }} mt-4">Jawatan Penerima</label>
                <input id="diterima_role" name="diterima_role" value="{{ old('diterima_role', $isEditing ? ($draft->diterima_role ?? '') : '') }}" class="{{ $inputClass }}">
                <div class="mt-10 border-t border-dashed border-gray-400 pt-2 text-sm text-gray-500 dark:border-gray-600">Ruang Tandatangan &amp; Cop</div>
            </div>
        </div>
    </x-common.document-card>

    <div class="flex flex-wrap items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <a href="{{ route('sebut-harga') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Kembali ke Senarai</a>
        @if (! $submitAtTop)
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600">{{ $isEditing ? 'Kemaskini' : 'Simpan Sebut Harga' }}</button>
        @endif
    </div>
</form>
