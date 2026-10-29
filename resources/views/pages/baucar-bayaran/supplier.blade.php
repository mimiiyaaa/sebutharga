@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title" :subtitle="__('Lengkapkan maklumat baucar, pembekal dan bayaran seperti borang PO.')">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.supplier.create') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
        <form method="POST" action="{{ route('baucar-bayaran.supplier.store') }}" class="space-y-6" x-data="{ paymentMethod: @js(old('payment_method', '')), items: @js($initialSupplierItems), nextId: {{ count($initialSupplierItems) + 1 }}, subtotal(item) { return Math.round((Number(item.quantity) || 0) * (Number(item.price) || 0) * 100) / 100 }, get total() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) }, money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(value) }, addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) }, removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1) } }">
            @csrf
            <input type="hidden" name="supplier_quotation_id" value="{{ $quotation->supplier_quotation_id }}">
            <input type="hidden" name="supplier_id" value="{{ $quotation->supplier_id }}">
            <input type="hidden" name="items_json" :value="JSON.stringify(items)">
            @if ($errors->any())
                <div role="alert" class="rounded-xl bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400"><ul class="list-disc space-y-1 ps-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @php($inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90')
            @php($labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400')

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <x-common.document-card :title="__('Maklumat Syarikat')">
                    <label for="issuer_name" class="{{ $labelClass }}">{{ __('Nama Syarikat') }}</label>
                    <input id="issuer_name" value="{{ config('purchase_order.issuer_name') }}" readonly class="{{ $inputClass }}">
                    <label for="issuer_address" class="{{ $labelClass }}">{{ __('Alamat Syarikat') }}</label>
                    <textarea id="issuer_address" rows="3" readonly class="{{ $inputClass }} h-auto">{{ config('purchase_order.issuer_address') }}</textarea>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="issuer_phone" class="{{ $labelClass }}">{{ __('Nombor Telefon') }}</label><input id="issuer_phone" value="{{ config('purchase_order.issuer_phone') }}" readonly class="{{ $inputClass }}"></div><div><label for="issuer_email" class="{{ $labelClass }}">{{ __('E-mel') }}</label><input id="issuer_email" value="{{ config('purchase_order.issuer_email') }}" readonly class="{{ $inputClass }}"></div></div>
                </x-common.document-card>
                <x-common.document-card :title="__('Maklumat Baucar')">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="voucher_no" class="{{ $labelClass }}">{{ __('No. Baucar') }}</label><input id="voucher_no" name="voucher_no" value="{{ old('voucher_no', $nextVoucherNo) }}" readonly required maxlength="100" class="{{ $inputClass }} bg-gray-50 dark:bg-gray-800/50">@error('voucher_no')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div><div><label for="voucher_date" class="{{ $labelClass }}">{{ __('Tarikh Baucar') }}</label><input id="voucher_date" name="voucher_date" type="date" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required class="{{ $inputClass }}">@error('voucher_date')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div></div>
                    <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10"><p class="text-xs font-medium uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Sebut Harga Dipautkan') }}</p><p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $quotation->quotation_no_supplier }}</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $quotation->quotation_title ?: __('Tiada tajuk') }}</p><a href="{{ route('baucar-bayaran.supplier.create') }}" class="mt-2 inline-block text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">{{ __('Tukar Pilihan') }}</a></div>
                </x-common.document-card>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <x-common.document-card :title="__('Kepada: Pembekal')">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="supplier_name" class="{{ $labelClass }}">{{ __('Nama Pembekal / Syarikat') }}</label><input id="supplier_name" value="{{ $quotation->supplier_name }}" readonly class="{{ $inputClass }}"></div><div><label for="supplier_person" class="{{ $labelClass }}">{{ __('Untuk Perhatian') }}</label><input id="supplier_person" value="{{ $quotation->person_in_charge ?: '—' }}" readonly class="{{ $inputClass }}"></div><div><label for="supplier_phone" class="{{ $labelClass }}">{{ __('Nombor Telefon') }}</label><input id="supplier_phone" value="{{ $quotation->supplier_phone ?: '—' }}" readonly class="{{ $inputClass }}"></div><div><label for="supplier_email" class="{{ $labelClass }}">{{ __('E-mel') }}</label><input id="supplier_email" value="{{ $quotation->supplier_email ?: '—' }}" readonly class="{{ $inputClass }}"></div><div class="sm:col-span-2"><label for="supplier_address" class="{{ $labelClass }}">{{ __('Alamat Pembekal') }}</label><textarea id="supplier_address" rows="3" readonly class="{{ $inputClass }} h-auto">{{ $quotation->supplier_address ?: '—' }}</textarea></div></div>
                </x-common.document-card>
                <x-common.document-card :title="__('Maklumat Bayaran')">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="payment_method" class="{{ $labelClass }}">{{ __('Cara Bayaran') }}</label><select id="payment_method" name="payment_method" x-model="paymentMethod" required class="{{ $inputClass }}"><option value="">{{ __('Pilih cara bayaran') }}</option>@foreach(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'] as $method)<option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ __($method) }}</option>@endforeach</select></div><div><label for="total_amount" class="{{ $labelClass }}">{{ __('Jumlah Dibayar (RM)') }}</label><input id="total_amount" name="total_amount" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('total_amount', number_format((float) $quotation->total_amount, 2, '.', '')) }}" required class="{{ $inputClass }}">@error('total_amount')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div><div x-cloak x-show="paymentMethod === 'Cek'" class="sm:col-span-2"><label for="cheque_no" class="{{ $labelClass }}">{{ __('No. Cek') }}</label><input id="cheque_no" name="cheque_no" value="{{ $chequeNo }}" maxlength="100" :required="paymentMethod === 'Cek'" class="{{ $inputClass }}">@error('cheque_no')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror</div><div class="sm:col-span-2"><label for="description" class="{{ $labelClass }}">{{ __('Keterangan') }}</label><textarea id="description" name="description" rows="5" maxlength="5000" class="{{ $inputClass }} h-auto">{{ old('description') }}</textarea></div></div>
                </x-common.document-card>
            </div>

            <x-common.document-card :title="__('Butiran Pembayaran')">
                <div class="overflow-x-auto">
                    <div class="item-editor-grid grid min-w-[900px] w-full grid-cols-12 overflow-hidden rounded-xl text-theme-sm text-gray-700 dark:text-gray-300">
                        <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-center text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Bil.') }}</div>
                        <div class="col-span-4 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Keterangan / Description') }}</div>
                        <div class="col-span-2 whitespace-nowrap bg-error-800 px-2 py-3 text-end text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Kuantiti') }}</div>
                        <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-start text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Unit') }}</div>
                        <div class="col-span-2 whitespace-nowrap bg-error-800 px-2 py-3 text-end text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Harga Seunit (RM)') }}</div>
                        <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-end text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Jumlah (RM)') }}</div>
                        <div class="col-span-1 whitespace-nowrap bg-error-800 px-2 py-3 text-center text-theme-xs font-semibold text-white dark:bg-error-900">{{ __('Tindakan') }}</div>
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="col-span-12 grid grid-cols-12 items-center border-b border-gray-100 py-3 dark:border-gray-800">
                                <div class="col-span-1 px-2 text-center tabular-nums" x-text="index + 1"></div>
                                <div class="col-span-4 px-1"><textarea rows="3" x-model="item.description" required maxlength="2000" aria-label="{{ __('Keterangan / Description') }}" class="{{ $inputClass }} w-full max-w-full"></textarea></div>
                                <div class="col-span-2 px-1"><input type="number" min="1" step="1" required x-model.number="item.quantity" aria-label="{{ __('Kuantiti') }}" class="{{ $inputClass }} w-full text-center"></div>
                                <div class="col-span-1 px-1"><input required maxlength="50" x-model="item.unit" aria-label="{{ __('Unit') }}" class="{{ $inputClass }} w-full text-center"></div>
                                <div class="col-span-2 px-1"><input type="number" min="0" step="0.01" required x-model.number="item.price" aria-label="{{ __('Harga Seunit (RM)') }}" class="{{ $inputClass }} w-full text-end"></div>
                                <div class="col-span-1 px-1 text-end font-semibold tabular-nums" x-text="money(subtotal(item))"></div>
                                <div class="col-span-1 px-1 text-center"><button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="document-action document-action-delete disabled:cursor-not-allowed disabled:opacity-40" title="{{ __('Buang') }}" aria-label="{{ __('Buang') }}"><x-svg.item-trash /></button></div>
                            </div>
                        </template>
                    </div>
                </div>
                <button type="button" @click="addItem()" class="mt-4 rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('+ Tambah Item') }}</button>
                <div class="ms-auto mt-5 max-w-sm border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-6 text-lg font-semibold text-gray-800 dark:text-gray-200"><span>{{ __('Jumlah Item') }}</span><span class="text-brand-600 dark:text-brand-400" x-text="money(total)"></span></div>
                </div>
            </x-common.document-card>

            <x-common.document-card :title="__('Pengesahan Baucar')">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2"><div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6"><div class="space-y-5"><div><label for="prepared_by" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Disediakan Oleh') }}</label><input id="prepared_by" name="prepared_by" value="{{ $preparedBy }}" required maxlength="255" class="{{ $inputClass }}"></div><div><label for="prepared_role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Jawatan / Unit Penyedia') }}</label><input id="prepared_role" name="prepared_role" value="{{ $preparedRole }}" required maxlength="255" class="{{ $inputClass }}"></div></div><div class="h-16" aria-hidden="true"></div><div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div></div><div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6"><div class="space-y-5"><div><label for="received_by" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Diterima Oleh') }}</label><input id="received_by" name="received_by" value="{{ $receivedBy }}" required maxlength="255" class="{{ $inputClass }}"></div><div><label for="received_role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Jawatan / Unit Penerima') }}</label><input id="received_role" name="received_role" value="{{ $receivedRole }}" required maxlength="255" class="{{ $inputClass }}"></div></div><div class="h-16" aria-hidden="true"></div><div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div></div></div>
            </x-common.document-card>

            <div class="flex flex-wrap items-center justify-end gap-3"><a href="{{ route('baucar-bayaran.supplier.create') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Batal') }}</a><button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan & Jana PDF') }}</button></div>
        </form>
    </x-common.document-workspace>
@endsection
