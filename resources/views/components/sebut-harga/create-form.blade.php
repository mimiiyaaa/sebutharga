@props([
    'customers' => collect(),
    'quotation' => null,
    'draft' => null,
    'editing' => false,
    'submitAtTop' => false,
])

@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
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

<form method="POST" action="{{ $formAction }}" class="space-y-6" x-data="{
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
        <x-common.component-card title="Maklumat Sebut Harga">
            <div>
                <label for="quotation_no" class="{{ $labelClass }}">No. Sebut Harga</label>
                <input id="quotation_no" readonly value="{{ $isEditing ? $quotation->quotation_no : 'Dijana secara automatik semasa disimpan' }}" class="{{ $inputClass }} bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            </div>
            <div>
                <label for="quotation_date" class="{{ $labelClass }}">Tarikh Sebut Harga</label>
                <input id="quotation_date" name="quotation_date" type="date" value="{{ $isEditing ? $quotation->quotation_date : now()->format('Y-m-d') }}" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="customer_id" class="{{ $labelClass }}">Nama Pelanggan</label>
                <select id="customer_id" name="customer_id" required class="{{ $inputClass }}">
                    <option value="">Pilih pelanggan</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}" @selected($isEditing && $quotation->customer_id == $customer->customer_id)>
                            {{ $customer->company_name }}
                            @if ($customer->customer_name)
                                - {{ $customer->customer_name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quotation_title" class="{{ $labelClass }}">Tajuk Sebut Harga</label>
                <input id="quotation_title" name="quotation_title" value="{{ $isEditing ? $quotation->quotation_title : '' }}" maxlength="255" placeholder="Contoh: Pembekalan peralatan pejabat" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="no_rujukan_pelanggan" class="{{ $labelClass }}">No. Rujukan Pelanggan</label>
                <input id="no_rujukan_pelanggan" name="no_rujukan_pelanggan" value="{{ $isEditing ? $quotation->no_rujukan_pelanggan : '' }}" maxlength="100" placeholder="Jika ada" class="{{ $inputClass }}">
            </div>
        </x-common.component-card>

        <x-common.component-card title="Status Dokumen">
            <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
                Sebut harga ini akan disimpan sebagai <strong>Draf</strong>. Anda masih boleh kemas kini sebelum dihantar atau dimuktamadkan.
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
        </x-common.component-card>
    </div>

    <x-common.component-card title="Item Sebut Harga">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        @foreach (['Keterangan', 'Kuantiti', 'Unit', 'Harga Seunit (RM)', 'Jumlah Kecil', 'Tindakan'] as $heading)
                            <th scope="col" class="whitespace-nowrap px-3 py-3 text-start font-medium">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="min-w-60 p-3"><input :name="`items[${index}][description]`" x-model="item.description" required class="{{ $inputClass }}" placeholder="Nama item / perkhidmatan"></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][quantity]`" type="number" min="1" step="1" x-model.number="item.quantity" required class="{{ $inputClass }}"></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][unit]`" x-model="item.unit" required class="{{ $inputClass }}" placeholder="Unit"></td>
                            <td class="min-w-40 p-3"><input :name="`items[${index}][price]`" type="number" min="0" step="0.01" x-model.number="item.price" required class="{{ $inputClass }}"></td>
                            <td class="whitespace-nowrap p-3 font-medium" x-text="money(subtotal(item))"></td>
                            <td class="p-3"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-error-400">Buang</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">+ Tambah Item</button>

        <div class="ms-auto max-w-sm space-y-4 text-sm text-gray-700 dark:text-gray-300" aria-live="polite">
            <div class="flex justify-between gap-4"><span>Jumlah Kasar</span><span x-text="money(gross)"></span></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-lg font-semibold dark:border-gray-700"><span>Jumlah Total</span><span x-text="money(gross)"></span></div>
        </div>
    </x-common.component-card>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ route('sebut-harga') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Kembali ke Senarai</a>
        @if (! $submitAtTop)
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600">{{ $isEditing ? 'Kemaskini' : 'Simpan Sebut Harga' }}</button>
        @endif
    </div>
</form>
