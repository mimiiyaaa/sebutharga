@props(['invoice' => false])
@php
    $inputClass = 'min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp
<x-common.document-card :title="$invoice ? __('Item Invois') : __('Item Penghantaran')" :desc="__('Masukkan keterangan, kuantiti dan unit bagi setiap item.')">
    <div class="custom-scrollbar overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 text-theme-xs uppercase text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-3">{{ __('Bil') }}</th>
                    <th scope="col" class="p-3 text-start">{{ __('Keterangan / Description') }}</th>
                    <th scope="col" class="p-3">{{ __('Kuantiti') }}</th>
                    <th scope="col" class="p-3">{{ __('Unit') }}</th>
                    @if ($invoice)
                        <th scope="col" class="p-3 text-end">{{ __('Harga Seunit (RM)') }}</th>
                        <th scope="col" class="p-3 text-end">{{ __('Jumlah (RM)') }}</th>
                    @endif
                    <th scope="col" class="p-3">{{ __('Tindakan') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(item, index) in items" :key="item.id">
                    <tr class="align-top">
                        <td class="p-3 text-center tabular-nums" x-text="index + 1"></td>
                        <td class="min-w-60 p-3"><textarea rows="3" x-model="item.description" :name="`items[${index}][description]`" aria-label="{{ __('Keterangan / Description') }}" placeholder="{{ __('Nama item / perkhidmatan') }}" class="{{ $inputClass }}"></textarea></td>
                        <td class="min-w-32 p-3"><input type="number" min="0.01" step="0.01" x-model.number="item.quantity" :name="`items[${index}][quantity]`" aria-label="{{ __('Kuantiti') }}" class="{{ $inputClass }} text-center"></td>
                        <td class="min-w-32 p-3"><input x-model="item.unit" :name="`items[${index}][unit]`" aria-label="{{ __('Unit') }}" class="{{ $inputClass }} text-center"></td>
                        @if ($invoice)
                            <td class="min-w-40 p-3"><input type="number" min="0" step="0.01" x-model.number="item.price" :name="`items[${index}][price]`" aria-label="{{ __('Harga Seunit (RM)') }}" class="{{ $inputClass }} price-input text-end"></td>
                            <td class="whitespace-nowrap p-3 pt-6 text-end tabular-nums" x-text="money(subtotal(item))"></td>
                        @endif
                        <td class="p-3"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="min-h-11 px-2 text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-error-400">{{ __('Buang') }}</button></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2.5 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('+ Tambah Item') }}</button>
    @if ($invoice)
        <div class="ms-auto max-w-sm space-y-4 rounded-xl border border-gray-200 bg-gray-50/50 p-5 text-sm dark:border-gray-700 dark:bg-gray-800/30" aria-live="polite">
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah Kasar') }}</span><span x-text="money(gross)"></span></div>
            <x-supplier-document.field name="discount_percent" :label="__('Diskaun (%)')" type="number" min="0" max="100" step="0.01" x-model.number="discount" />
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah Diskaun') }}</span><span x-text="money(discountAmount)"></span></div>
            <x-supplier-document.field name="sst_percent" :label="__('SST (%)')" type="number" min="0" max="100" step="0.01" x-model.number="sst" />
            <div class="flex justify-between gap-4"><span>{{ __('Jumlah SST') }}</span><span x-text="money(sstAmount)"></span></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white/90"><span>{{ __('Jumlah Bersih') }}</span><span x-text="money(net)"></span></div>
        </div>
    @else
        <div class="flex flex-wrap justify-end gap-6 rounded-xl bg-gray-50 p-4 text-sm dark:bg-gray-800/40" aria-live="polite">
            <span>{{ __('Bilangan Item') }}: <strong x-text="items.length"></strong></span>
            <span>{{ __('Jumlah Kuantiti') }}: <strong x-text="quantity"></strong></span>
        </div>
    @endif
</x-common.document-card>
