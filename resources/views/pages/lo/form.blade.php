@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $lo ? __('Kemaskini LO') : __('Tambah LO') }}</h2>
        <a href="{{ $lo ? route('lo.show', $lo->lo_inden_id) : route('lo') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
    </div>
    @php
        $selectedQuotationId = (string) old('quotation_detail_id', $lo->quotation_detail_id ?? '');
        $quotationOptions = $finalQuotations->map(fn ($quotation) => [
            'id' => (string) $quotation->quotation_detail_id,
            'customer' => $quotation->company_name ?: $quotation->customer_name,
            'number' => $quotation->quotation_no,
            'version' => $quotation->final_no,
            'title' => $quotation->quotation_title,
            'amount' => (string) $quotation->jumlah_total,
            'status' => $quotation->lo_inden_no ? 'Berjaya — LO Diterima' : ((int) $quotation->status_quotation === 1 ? 'Berjaya — Belum Ada LO' : 'Menunggu LO'),
        ])->values();
    @endphp
    <form method="POST" enctype="multipart/form-data" action="{{ $lo ? route('lo.update', $lo->lo_inden_id) : route('lo.store') }}" class="space-y-6 font-outfit" x-data="{ quotationId: @js($selectedQuotationId), quotationFilter: '', quotations: @js($quotationOptions), amount: @js((string) old('amount', $lo->amount ?? '0.00')), fileName: '', get selectedQuotation() { return this.quotations.find(quotation => quotation.id === String(this.quotationId)); }, get filteredQuotations() { return this.quotations.filter(quotation => !this.quotationFilter || quotation.status === this.quotationFilter); }, selectQuotation() { if (this.selectedQuotation) this.amount = this.selectedQuotation.amount; } }">
        @csrf
        @if($lo) @method('PUT') @endif
        <x-common.document-card :title="$lo ? __('Maklumat LO') : __('Maklumat LO Baharu')">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="quotation_filter" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tapis Status Sebut Harga') }}</label>
                    <select id="quotation_filter" x-model="quotationFilter" class="mb-5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"><option value="">{{ __('Semua status') }}</option><option value="Menunggu LO">{{ __('Menunggu LO') }}</option><option value="Berjaya — Belum Ada LO">{{ __('Berjaya — Belum Ada LO') }}</option></select>
                    <label for="quotation_detail_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Sebut Harga Final') }} <span class="text-error-500">*</span></label>
                    <select id="quotation_detail_id" name="quotation_detail_id" required x-model="quotationId" @change="selectQuotation" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">{{ __('Pilih Sebut Harga Final yang menunggu LO') }}</option>
                        <template x-for="quotation in filteredQuotations" :key="quotation.id"><option :value="quotation.id" x-text="`${quotation.number} · Versi ${quotation.version} · ${quotation.customer} · ${quotation.status}`"></option></template>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih quotation yang menunggu LO atau telah berjaya tetapi belum menerima LO.') }}</p>
                    @error('quotation_detail_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div x-show="selectedQuotation" x-cloak class="rounded-xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-500/20 dark:bg-brand-500/5 md:col-span-2"><p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`${selectedQuotation?.number || ''} · Versi ${selectedQuotation?.version || ''}`"></p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="selectedQuotation?.title || '—'"></p></div>
                <div><label for="lo_inden_no" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('No. LO / Inden') }} <span class="text-error-500">*</span></label><input id="lo_inden_no" name="lo_inden_no" value="{{ old('lo_inden_no', $lo->lo_inden_no ?? '') }}" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('lo_inden_no')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror</div>
                <div><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Pelanggan / Syarikat') }}</label><div class="flex min-h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="selectedQuotation?.customer || '—'"></div></div>
                <div><label for="lo_inden_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh LO') }} <span class="text-error-500">*</span></label><input id="lo_inden_date" name="lo_inden_date" type="date" value="{{ old('lo_inden_date', isset($lo) ? \Carbon\Carbon::parse($lo->lo_inden_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('lo_inden_date')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror</div>
                <div><label for="amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Nilai LO (RM)') }} <span class="text-error-500">*</span></label><input id="amount" name="amount" type="number" min="0" step="0.01" x-model="amount" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">@error('amount')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror</div>
                <div class="md:col-span-2"><label for="document_file" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Dokumen LO') }} @if(! $lo)<span class="text-error-500">*</span>@endif</label><input id="document_file" name="document_file" type="file" accept="application/pdf,image/jpeg,image/png" @if(! $lo) required @endif class="sr-only" @change="fileName = $event.target.files[0]?.name || ''"><label for="document_file" class="flex cursor-pointer flex-col gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 transition hover:border-brand-400 hover:bg-brand-50/40 dark:border-gray-700 dark:bg-gray-900/40 dark:hover:border-brand-500 dark:hover:bg-brand-500/5 sm:flex-row sm:items-center sm:justify-between"><span class="min-w-0"><span class="block text-sm font-medium text-gray-800 dark:text-white/90" x-text="fileName || '{{ __('Pilih fail PDF atau imej') }}'"></span><span class="mt-0.5 block truncate text-xs text-gray-500 dark:text-gray-400" x-text="fileName ? '{{ __('Fail telah dipilih') }}' : '{{ __('PDF, JPG, JPEG atau PNG · Maksimum 10MB') }}'"></span></span><span class="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white">{{ __('Pilih Fail') }}</span></label>@if($lo?->document_file)<a href="{{ route('lo.document', $lo->lo_inden_id) }}" target="_blank" rel="noopener" class="mt-3 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ __('Lihat dokumen semasa') }}</a>@endif @error('document_file')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror</div>
            </div>
        </x-common.document-card>
        <div class="flex justify-end"><button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">{{ $lo ? __('Simpan Kemaskini') : __('Simpan LO') }}</button></div>
    </form>
@endsection
