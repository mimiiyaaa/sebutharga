@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="$title">
        <x-slot:referenceActions>
            <a href="{{ route($backRoute) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>

        <x-common.document-card :title="$documentType" :desc="__('Sediakan dokumen baharu untuk pelanggan.')">
            <form method="GET" action="{{ route($createRoute) }}" class="space-y-5" x-data="{ selectedLo: @js((string) old('lo_inden_id', '')) }">
                <div>
                    <label for="lo_inden_id" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Pilih Local Order (LO)') }}</label>
                    <select id="lo_inden_id" name="lo_inden_id" required x-model="selectedLo" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">{{ __('Pilih Local Order (LO)') }}</option>
                        @foreach($localOrders as $lo)
                            <option value="{{ $lo->lo_inden_id }}">{{ $lo->lo_inden_no }} — {{ $lo->company_name ?: $lo->customer_name }} — {{ \Carbon\Carbon::parse($lo->lo_inden_date)->format('d/m/Y') }}</option>
                        @endforeach
                    </select>
                    @error('lo_inden_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih LO dahulu. Maklumat pelanggan dan item sebut harga berkaitan akan diisi dalam borang.') }}</p>
                </div>
                @if($localOrders->isEmpty())
                    <p class="rounded-lg bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Tiada Local Order tersedia. Tambah LO terlebih dahulu.') }}</p>
                @endif
                <div class="flex justify-end">
                    <button type="submit" :disabled="!selectedLo" @disabled($localOrders->isEmpty()) class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ $createButtonLabel }}</button>
                </div>
            </form>
        </x-common.document-card>
    </x-common.document-workspace>
@endsection
