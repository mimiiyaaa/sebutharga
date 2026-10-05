@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('syarikat') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
            ← {{ __('Kembali') }}
        </a>
    </div>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 via-white to-white px-6 py-7 dark:border-gray-800 dark:from-brand-500/10 dark:via-gray-900 dark:to-gray-900 sm:px-8">
                <div class="flex flex-wrap items-center gap-5">
                    <div class="flex h-24 w-32 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                        @if ($company->logo_path)
                            <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ __('Logo Syarikat') }}" class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-center text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Tiada logo') }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-brand-600 dark:text-brand-400">{{ __('Profil Syarikat') }}</p>
                        <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $company->nama_syarikat }}</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Maklumat syarikat yang digunakan dalam dokumen sebut harga.') }}</p>
                    </div>
                    <a href="{{ route('companies.edit', $company->company_id) }}" class="ms-auto inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                        {{ __('Edit Syarikat') }}
                    </a>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 21h16M5 21V5.5A1.5 1.5 0 0 1 6.5 4h11A1.5 1.5 0 0 1 19 5.5V21M8 8h2m4 0h2M8 12h2m4 0h2M8 16h2m4 0h2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Utama') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Butiran asas syarikat') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['label' => 'Nama Syarikat', 'value' => $company->nama_syarikat],
                        ['label' => 'Person In Charge', 'value' => $company->person_in_charge],
                        ['label' => 'No. Telefon', 'value' => $company->no_telefon],
                        ['label' => 'E-mel', 'value' => $company->emel],
                    ] as $item)
                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __($item['label']) }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-gray-800 dark:text-white/90">{{ $item['value'] ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Alamat Syarikat') }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm font-semibold leading-6 text-gray-800 dark:text-white/90">{{ $company->alamat_syarikat ?: '—' }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
