@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <a href="{{ route($backRoute) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
    </div>

    <div class="font-outfit" x-data="{
        documentUrl: '',
        documentIsImage: false,
        fileName: '',
        chooseDocument(event) {
            const file = event.target.files[0];
            if (!file) return;
            const acceptedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!acceptedTypes.includes(file.type)) { event.target.value = ''; return; }
            if (this.documentUrl && this.documentUrl.startsWith('blob:')) URL.revokeObjectURL(this.documentUrl);
            this.documentUrl = URL.createObjectURL(file);
            this.documentIsImage = file.type.startsWith('image/');
            this.fileName = file.name;
        }
    }">
        <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
            <section class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $documentPartyTitle }}</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $documentPartyDescription }}</p>
                    </div>
                    <label for="supplier_document" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Upload Dokumen') }}</label>
                    <input id="supplier_document" name="supplier_document" type="file" accept="application/pdf,image/jpeg,image/png" class="sr-only" @change="chooseDocument($event)">
                </div>
                <div class="border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                    <div x-cloak x-show="fileName" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <svg class="h-4 w-4 text-error-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2h8l4 4v16H6V2Zm7 1.5V7h3.5L13 3.5Z"/></svg>
                        <span class="truncate" x-text="fileName"></span>
                    </div>
                    <p x-show="!fileName" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada dokumen dipilih.') }}</p>
                </div>
                <div class="min-h-[360px] bg-gray-100 p-3 dark:bg-gray-950/40">
                    <div x-cloak x-show="documentUrl" class="h-[360px] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700">
                        <template x-if="documentIsImage"><img :src="documentUrl" alt="{{ $documentPartyTitle }}" class="h-full w-full object-contain"></template>
                        <template x-if="!documentIsImage"><iframe :src="documentUrl" title="{{ $documentPartyTitle }}" class="h-full w-full"></iframe></template>
                    </div>
                    <div x-show="!documentUrl" class="flex h-[360px] flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-8 text-center dark:border-gray-700 dark:bg-gray-900/50">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h7l4 4v14H7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M8 13h8M8 17h6"/></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview dokumen akan muncul di sini') }}</h3>
                        <p class="mt-2 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $previewDescription }}</p>
                        <label for="supplier_document" class="mt-5 cursor-pointer text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Pilih PDF atau imej') }}</label>
                    </div>
                </div>
            </section>

            <x-common.document-card :title="$documentType">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="supplier_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $partyNameLabel }}</label>
                        <input id="supplier_name" name="supplier_name" type="text" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label for="document_no" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $documentNumberLabel }}</label>
                        <input id="document_no" name="document_no" type="text" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label for="document_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh Dokumen') }}</label>
                        <input id="document_date" name="document_date" type="date" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label for="document_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Jumlah (RM)') }}</label>
                        <input id="document_amount" name="document_amount" type="number" min="0" step="0.01" class="min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>
            </x-common.document-card>
        </div>
    </div>
@endsection
