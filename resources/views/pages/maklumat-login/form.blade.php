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
                            <input id="{{ $field }}" name="{{ $field }}" type="password" @required($field === 'password' && ! $user) minlength="8" class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">
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
