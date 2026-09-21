@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$company ? __('Edit Syarikat') : __('Tambah Syarikat')" />

    <div class="w-full">
        <form method="POST" action="{{ route('companies.save', ['id' => $company?->company_id]) }}" class="space-y-6">
            @csrf

            <x-common.document-card
                :title="$company ? __('Kemaskini Maklumat Syarikat') : __('Maklumat Syarikat')"
                :desc="$company ? __('Kemaskini maklumat syarikat yang dipilih.') : __('Masukkan maklumat syarikat untuk digunakan dalam sebut harga.')"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nama_syarikat" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Syarikat') }} <span class="text-error-500">*</span></label>
                        <input id="nama_syarikat" name="nama_syarikat" type="text" value="{{ old('nama_syarikat', $company?->nama_syarikat) }}" placeholder="Contoh: ABC Holdings Sdn. Bhd." required class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90 dark:focus:bg-gray-900">
                        @error('nama_syarikat')<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2"><div class="border-t border-gray-100 pt-1 dark:border-gray-800"></div><h3 class="mt-3 text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Perhubungan') }}</h3></div>

                    @foreach (['no_telefon' => 'No. Telefon', 'emel' => 'E-mel', 'person_in_charge' => 'Person In Charge'] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($label) }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'emel' ? 'email' : ($field === 'no_telefon' ? 'tel' : 'text') }}" value="{{ old($field, $company?->$field) }}" class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90 dark:focus:bg-gray-900">
                            @error($field)<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    <div class="md:col-span-2">
                        <div class="mb-1 border-t border-gray-100 pt-1 dark:border-gray-800"></div>
                        <label for="alamat_syarikat" class="mt-3 block text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Alamat Syarikat') }}</label>
                        <textarea id="alamat_syarikat" name="alamat_syarikat" rows="4" placeholder="Contoh: No. 12, Jalan ..." class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90 dark:focus:bg-gray-900">{{ old('alamat_syarikat', $company?->alamat_syarikat) }}</textarea>
                        @error('alamat_syarikat')<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-common.document-card>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
                <a href="{{ route('syarikat') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan') }}</button>
            </div>
        </form>
    </div>
@endsection
