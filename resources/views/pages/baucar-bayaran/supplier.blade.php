@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <form method="POST" action="{{ route('baucar-bayaran.supplier.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ fileName: '', chooseFile(event) { this.fileName = event.target.files[0]?.name || ''; } }">
        @csrf
        <input type="hidden" name="supplier_quotation_id" value="{{ $quotation->supplier_quotation_id }}">
        <input type="hidden" name="supplier_id" value="{{ $quotation->supplier_id }}">
        @if ($errors->any())
            <div role="alert" class="rounded-xl bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400">
                <ul class="list-disc space-y-1 ps-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
            <x-common.document-card :title="__('Maklumat Baucar Pembekal')" :desc="__('Baucar ini dipautkan kepada sebut harga pembekal yang dipilih.')">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-brand-100 bg-brand-50 p-4 md:col-span-2 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><p class="text-xs font-medium uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Sebut Harga Pembekal Dipilih') }}</p><p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $quotation->quotation_no_supplier }}</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $quotation->supplier_name }} · {{ $quotation->quotation_title ?: __('Tiada tajuk') }}</p></div>
                            <a href="{{ route('baucar-bayaran.supplier.create') }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">{{ __('Tukar Pilihan') }}</a>
                        </div>
                        <p class="mt-3 text-sm font-semibold tabular-nums text-gray-800 dark:text-gray-200">{{ __('Jumlah Sebut Harga') }}: RM {{ number_format((float) $quotation->total_amount, 2) }}</p>
                    </div>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('No. Baucar') }}</span>
                        <input name="voucher_no" value="{{ old('voucher_no') }}" required maxlength="100" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh') }}</span>
                        <input name="voucher_date" type="date" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Cara Bayaran') }}</span>
                        <select name="payment_method" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">{{ __('Pilih cara bayaran') }}</option>
                            @foreach (['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ __($method) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Amaun (RM)') }}</span>
                        <input name="total_amount" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('total_amount', number_format((float) $quotation->total_amount, 2, '.', '')) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Keterangan') }}</span>
                        <textarea name="description" rows="5" maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description') }}</textarea>
                    </label>
                </div>
            </x-common.document-card>

            <x-common.document-card :title="__('Dokumen Baucar')" :desc="__('Upload PDF atau imej baucar bayaran asal.')">
                <label for="document_file" class="flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 text-center transition hover:border-brand-400 hover:bg-brand-50/40 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:border-brand-500">
                    <svg class="mb-4 h-10 w-10 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 16V4m0 0L8 8m4-4 4 4M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" /></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="fileName || '{{ __('Pilih PDF atau imej') }}'"></span>
                    <span class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('PDF, JPG atau PNG sehingga 10 MB') }}</span>
                </label>
                <input id="document_file" name="document_file" type="file" accept="application/pdf,image/jpeg,image/png" required class="sr-only" @change="chooseFile($event)">
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan Baucar Pembekal') }}</button>
                </div>
            </x-common.document-card>
        </div>
    </form>
@endsection
