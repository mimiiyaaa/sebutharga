@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$user ? __('Edit Pengguna') : __('Tambah Pengguna')" />
    <div class="w-full">
        <form method="POST" action="{{ route('maklumat-login.save', ['id' => $user?->id]) }}" class="space-y-6">
            @csrf
            <x-common.document-card :title="$user ? __('Kemaskini Maklumat Akaun') : __('Maklumat Akaun')" :desc="__('Lengkapkan maklumat login pengguna.')">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @foreach (['name' => 'Nama Pengguna', 'no_kp' => 'No. Kad Pengenalan', 'email' => 'E-mel'] as $field => $label)
                        <div @class(['md:col-span-2' => $field === 'email'])>
                            <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($label) }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field, $user?->$field) }}" required class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">
                            @error($field)<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div class="md:col-span-2"><div class="border-t border-gray-100 pt-1 dark:border-gray-800"></div><h3 class="mt-3 text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Keselamatan Akaun') }}</h3></div>
                    @foreach (['password' => 'Kata Laluan', 'password_confirmation' => 'Sahkan Kata Laluan'] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($label) }} @if($field === 'password' && $user)<span class="font-normal text-gray-500">(kosongkan jika tidak berubah)</span>@endif</label>
                            <div x-data="{ visible: false }" class="relative">
                                <input id="{{ $field }}" name="{{ $field }}" :type="visible ? 'text' : 'password'" @required($field === 'password' && ! $user) minlength="8" class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 pe-12 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">
                                <button type="button" @click="visible = !visible" :aria-label="visible ? 'Sembunyikan kata laluan' : 'Lihat kata laluan'" class="absolute end-0 top-0 flex h-11 w-11 items-center justify-center text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                                    <svg x-show="!visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5" stroke-width="1.8"/></svg>
                                    <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.1A10.6 10.6 0 0 1 12 5c6 0 9.5 7 9.5 7a17.8 17.8 0 0 1-3.1 3.8M6.2 6.2C3.8 8 2.5 12 2.5 12S6 19 12 19a9.7 9.7 0 0 0 3.3-.6"/></svg>
                                </button>
                            </div>
                            @error($field)<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </x-common.document-card>
            <div class="flex justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
                <a href="{{ route('maklumat-login') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan Pengguna') }}</button>
            </div>
        </form>
    </div>
@endsection
