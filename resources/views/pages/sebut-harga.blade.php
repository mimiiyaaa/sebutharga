@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sebut Harga" />

    @php
        $existingQuotationSources = $quotations->flatMap(function ($quotation) {
            return collect($quotation['versions'])->map(function ($version) use ($quotation) {
                return [
                    'id' => $version['id'],
                    'quotationId' => $quotation['id'],
                    'quotationNo' => $quotation['quotationNo'],
                    'customerName' => $quotation['customerName'],
                    'title' => $version['title'],
                    'draftNo' => $version['draftNo'],
                    'status' => $version['status'],
                    'date' => $version['date'],
                    'editUrl' => route('sebut-harga.create', ['source' => $version['id']]),
                ];
            });
        })->values();
    @endphp

    <div class="space-y-6" x-data="{ addQuotationOpen: false, addMode: null, sourceType: 'Draf', sources: {{ Illuminate\Support\Js::from($existingQuotationSources) }} }" @keydown.escape.window="addQuotationOpen = false">
        <x-common.component-card title="Senarai Sebut Harga">
            <x-slot:header>
                <button type="button" @click="addQuotationOpen = true" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                    + {{ __('Tambah Sebut Harga') }}
                </button>
            </x-slot:header>
            <x-tables.basic-tables.sebutharga-table :quotations="$quotations" />
        </x-common.component-card>

        <div x-cloak x-show="addQuotationOpen" x-transition.opacity class="quotation-add-overlay fixed inset-0 z-999999 flex items-center justify-center overflow-hidden bg-gray-900/50 p-4 sm:p-8" @click.self="addQuotationOpen = false">
            <section x-show="addQuotationOpen" x-transition class="quotation-add-modal flex max-h-[calc(100dvh-2rem)] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-700 dark:bg-gray-900" role="dialog" aria-modal="true" aria-labelledby="add-quotation-title">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
                    <div>
                        <h2 id="add-quotation-title" class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah Sebut Harga') }}</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih borang kosong atau sebut harga sedia ada.') }}</p>
                    </div>
                    <button type="button" @click="addQuotationOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-xl text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}">&times;</button>
                </div>

                <div class="min-h-0 space-y-4 overflow-y-auto p-4 sm:p-5">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <a href="{{ route('sebut-harga.create') }}" class="group rounded-xl border-2 border-brand-200 bg-brand-50/50 p-4 transition hover:border-brand-500 hover:bg-brand-50 dark:border-brand-700/60 dark:bg-brand-500/10 dark:hover:bg-brand-500/15">
                            <span class="flex items-center justify-between gap-3">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Buat Sebut Harga Baru') }}</span>
                                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ __('Buka borang kosong dan isi dari awal.') }}</span>
                                </span>
                                <span class="text-xl text-brand-500">+</span>
                            </span>
                        </a>

                        <button type="button" @click="addMode = 'existing'" class="rounded-xl border-2 border-gray-200 bg-white p-4 text-start transition hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800">
                            <span class="block text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Guna Sebut Harga Sedia Ada') }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih Draf atau Final untuk buka borang edit.') }}</span>
                        </button>
                    </div>

                    <div x-show="addMode === 'existing'" x-transition class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="flex flex-col gap-3 border-b border-gray-100 pb-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Sebut Harga Sedia Ada') }}</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih kategori dan rekod.') }}</p>
                            </div>
                            <div class="inline-flex rounded-lg border border-gray-200 p-1 dark:border-gray-700">
                                <button type="button" @click="sourceType = 'Draf'" :class="sourceType === 'Draf' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300'" class="rounded-md px-3 py-1.5 text-xs font-medium transition">{{ __('Draf') }}</button>
                                <button type="button" @click="sourceType = 'Final'" :class="sourceType === 'Final' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300'" class="rounded-md px-3 py-1.5 text-xs font-medium transition">{{ __('Final') }}</button>
                            </div>
                        </div>

                        <div class="mt-3 max-h-52 overflow-y-auto">
                            <template x-for="source in sources.filter(item => item.status === sourceType)" :key="source.id">
                                <a :href="source.editUrl" class="mb-2 block rounded-lg border border-gray-200 p-3 transition hover:border-brand-400 hover:bg-brand-50/50 dark:border-gray-700 dark:hover:border-brand-600 dark:hover:bg-brand-500/10">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="source.quotationNo + ' — ' + source.customerName"></p>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="source.title + ' · ' + source.date"></p>
                                        </div>
                                        <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="source.status + ' ' + source.draftNo"></span>
                                    </div>
                                </a>
                            </template>
                            <div x-show="sources.filter(item => item.status === sourceType).length === 0" class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                {{ __('Tiada sebut harga dalam kategori ini.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
