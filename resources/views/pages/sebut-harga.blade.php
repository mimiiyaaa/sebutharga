@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sebut Harga Draf" />

    <div class="space-y-6" x-data="{
        addQuotationOpen: false,
    }" @keydown.escape.window="addQuotationOpen = false">
        <x-common.component-card title="Senarai Sebut Harga Draf">
            <x-slot:header>
                <button type="button" @click="addQuotationOpen = true" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                    + {{ __('Tambah Sebut Harga Draf') }}
                </button>
            </x-slot:header>
            <x-tables.basic-tables.sebutharga-table :quotations="$quotations" />
        </x-common.component-card>

        <div x-cloak x-show="addQuotationOpen" x-transition.opacity class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4 sm:p-8" @click.self="addQuotationOpen = false">
            <section x-show="addQuotationOpen" x-transition class="w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-700 dark:bg-gray-900" role="dialog" aria-modal="true" aria-labelledby="add-quotation-title">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                    <div>
                        <h2 id="add-quotation-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah Sebut Harga Draf') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih cara untuk mula.') }}</p>
                    </div>
                    <button type="button" @click="addQuotationOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-xl text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}">&times;</button>
                </div>

                <div class="max-h-[calc(100dvh-8rem)] space-y-4 overflow-y-auto p-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <a href="{{ route('sebut-harga.create') }}" class="group rounded-xl border-2 border-brand-200 bg-brand-50/50 p-4 transition hover:border-brand-500 hover:bg-brand-50 dark:border-brand-700/60 dark:bg-brand-500/10 dark:hover:bg-brand-500/15">
                            <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Buat Sebut Harga Baru') }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Terus buka form kosong.') }}</p></div><span class="text-2xl text-brand-500">→</span></div>
                        </a>
                        <a href="{{ route('sebut-harga', ['add' => 1]) }}" class="group rounded-xl border-2 border-gray-200 bg-white p-4 text-start transition hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800">
                            <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Guna Sebut Harga Sedia Ada') }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Buka senarai Draf atau Final.') }}</p></div><span class="text-xl text-gray-400 transition group-hover:translate-x-1">→</span></div>
                        </a>
                    </div>

                </div>
            </section>
        </div>
    </div>
@endsection
