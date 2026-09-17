@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="__('Tambah PO')" :subtitle="__('Pilih nombor sebut harga sebelum meneruskan.')">
<x-common.document-card :title="__('Pilih Sebut Harga')">
    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Semua sebut harga Final berkeputusan Setuju disenaraikan mengikut nombor sebut harga.') }}</p>
    @if ($errors->any())
        <p role="alert" class="text-error-600 dark:text-error-400">{{ __('Sila pilih sebut harga Final yang dipersetujui.') }}</p>
    @endif
    @if ($quotations->isEmpty())
        <p class="rounded-xl bg-warning-50 p-4 text-theme-sm text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Belum ada sebut harga Final yang dipersetujui.') }}</p>
    @endif
    <form method="GET" action="{{ route('purchase-order.form') }}" class="space-y-5" x-data="{ draftId: {{ Illuminate\Support\Js::from((string) old('quotation_detail_id', '')) }}, quotations: {{ Illuminate\Support\Js::from($quotations) }}, get quotation() { return this.quotations.find(row => String(row.quotation_detail_id) === String(this.draftId)) } }">
        <label for="quotation_detail_id" class="block text-sm text-gray-700 dark:text-gray-300">{{ __('No. Sebut Harga') }}</label>
        <select id="quotation_detail_id" name="quotation_detail_id" required x-model="draftId" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">{{ __('Pilih Sebut Harga') }}</option>
            @foreach ($quotations as $customer)
                <option value="{{ $customer->quotation_detail_id }}">{{ $customer->quotation_no }} — {{ $customer->company_name }} — {{ __('Draf') }} {{ $customer->draft_no }}</option>
            @endforeach
        </select>
        <div x-cloak x-show="quotation" class="rounded-xl border border-success-200 bg-success-50/50 p-5 text-gray-800 dark:border-success-800 dark:bg-success-500/10 dark:text-gray-200">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h3 class="font-semibold">{{ __('Sebut Harga Dipersetujui') }}</h3><span class="rounded-full bg-success-100 px-3 py-1 text-theme-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">{{ __('Setuju') }}</span></div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (['quotation_no' => 'No. Sebut Harga', 'quotation_id' => 'ID Sebut Harga', 'quotation_detail_id' => 'ID Draf', 'draft_no' => 'Draf', 'quotation_date' => 'Tarikh', 'quotation_title' => 'Tajuk'] as $field => $label)
                <div><p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($label) }}</p><span class="text-theme-sm font-medium" x-text="quotation?.{{ $field }} || '—'"></span></div>
            @endforeach
            </div>
        </div>
        <p x-cloak x-show="draftId && !quotation" class="text-gray-600 dark:text-gray-400">{{ __('Tiada sebut harga Final yang dipersetujui untuk pelanggan ini.') }}</p>
        <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
            <a href="{{ route('purchase-order.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Kembali ke Senarai PO') }}</a>
            <button type="submit" :disabled="!quotation" class="rounded-lg bg-brand-500 px-5 py-3 text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-500 dark:text-white">{{ __('Teruskan') }}</button>
        </div>
    </form>
</x-common.document-card>
</x-common.document-workspace>
@endsection
