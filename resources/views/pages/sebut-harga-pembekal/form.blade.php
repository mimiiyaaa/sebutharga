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
        pdfUrl: {{ Illuminate\Support\Js::from($existingPdfUrl) }},
        fileName: {{ Illuminate\Support\Js::from($quotation?->file_name ?? '') }},
        choosePdf(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.type !== 'application/pdf') { event.target.value = ''; this.pdfUrl = ''; this.fileName = ''; return; }
            if (this.pdfUrl && this.pdfUrl.startsWith('blob:')) URL.revokeObjectURL(this.pdfUrl);
            this.pdfUrl = URL.createObjectURL(file);
            this.fileName = file.name;
        },
        addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) },
        subtotal(item) { return (Number(item.quantity) || 0) * (Number(item.price) || 0) },
        get total() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) },
        money(value) { return new Intl.NumberFormat('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) }
    }">
        @csrf
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            <section class="lg:sticky lg:top-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Dokumen Pembekal') }}</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Rujuk PDF ini semasa mengisi maklumat di sebelah kanan.') }}</p></div>
                    <label for="supplier_quotation_file" class="inline-flex cursor-pointer items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Upload PDF') }}</label>
                    <input id="supplier_quotation_file" name="supplier_quotation_file" type="file" accept="application/pdf" class="sr-only" @change="choosePdf($event)">
                </div>
                <div class="border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <div x-cloak x-show="fileName" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300"><svg class="h-4 w-4 text-error-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2h8l4 4v16H6V2Zm7 1.5V7h3.5L13 3.5Z"/></svg><span class="truncate" x-text="fileName"></span></div>
                    <p x-show="!fileName" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada PDF dipilih.') }}</p>
                </div>
                <div class="min-h-[680px] bg-gray-100 p-3 dark:bg-gray-950/40">
                    <div x-cloak x-show="pdfUrl" class="h-[680px] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700"><iframe :src="pdfUrl" title="{{ __('Preview PDF Sebut Harga Pembekal') }}" class="h-full w-full"></iframe></div>
                    <div x-show="!pdfUrl" class="flex h-[680px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-8 text-center dark:border-gray-700 dark:bg-gray-900/50"><div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M8 13h8M8 17h6"/></svg></div><h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview PDF akan muncul di sini') }}</h3><p class="mt-2 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Upload sebut harga pembekal untuk melihat dokumen sambil memasukkan item di sebelah kanan.') }}</p><label for="supplier_quotation_file" class="mt-5 cursor-pointer text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Pilih fail PDF') }}</label></div>
                </div>
            </section>

            <div class="space-y-5">
                <x-common.document-card :title="__('Maklumat Sebut Harga')">
                    <div class="grid grid-cols-1 gap-4">
                        <div><label for="supplier_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Nama Syarikat') }}</label><select id="supplier_id" name="supplier_id" required class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="">{{ __('Pilih pembekal') }}</option>@foreach ($suppliers as $supplier)<option value="{{ $supplier->customer_id }}" @selected(old('supplier_id', $quotation?->supplier_id) == $supplier->customer_id)>{{ $supplier->company_name }}</option>@endforeach</select></div>
                        <div><label for="quotation_no_supplier" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('No. Sebut Harga Pembekal') }}</label><input id="quotation_no_supplier" name="quotation_no_supplier" required value="{{ old('quotation_no_supplier', $quotation?->quotation_no_supplier) }}" class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                        <div><label for="quotation_title" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tajuk Sebut Harga') }}</label><input id="quotation_title" name="quotation_title" value="{{ old('quotation_title', $quotation?->quotation_title) }}" class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                        <div><label for="person_in_charge" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Person In Charge') }}</label><input id="person_in_charge" name="person_in_charge" value="{{ old('person_in_charge', $quotation?->person_in_charge) }}" class="w-full min-h-11 rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                    </div>
                </x-common.document-card>

                <x-common.document-card :title="__('Item Sebut Harga')">
                    <div class="space-y-4">
                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="item.id">
                                <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300" x-text="index + 1"></span>
                                        <button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-xs font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-30 dark:text-error-400">{{ __('Buang') }}</button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Perihal') }}</label><textarea :name="`items[${index}][description]`" x-model="item.description" rows="2" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea></div>
                                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Kuantiti') }}</label><input :name="`items[${index}][quantity]`" x-model.number="item.quantity" type="number" min="1" step="1" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Unit') }}</label><input :name="`items[${index}][unit]`" x-model="item.unit" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Harga Seunit (RM)') }}</label><input :name="`items[${index}][price]`" x-model.number="item.price" type="number" min="0" step="0.01" required class="price-input w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
                                        <div><p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Jumlah (RM)') }}</p><p class="flex min-h-10 items-center rounded-lg bg-gray-50 px-3 text-sm font-semibold text-gray-800 dark:bg-gray-800 dark:text-white/90" x-text="'RM ' + money(subtotal(item))"></p></div>
                                    </div>
                                </section>
                            </template>
                        </div>
                        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-3 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">+ {{ __('Tambah Item') }}</button>
                        <div class="flex justify-end border-t border-gray-100 pt-4 text-sm dark:border-gray-800"><span class="font-semibold text-gray-800 dark:text-white/90">{{ __('Jumlah Total') }}: RM <span x-text="money(total)"></span></span></div>
                    </div>
                </x-common.document-card>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sebut-harga-pembekal') }}" aria-label="{{ __('Kembali ke Senarai Sebut Harga Pembekal') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        {{ __('Kembali') }}
                    </a>
                    <button class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection
