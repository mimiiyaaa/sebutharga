@extends('layouts.app')

@section('content')
    @php
        $isCustomer = $type === 'pelanggan';
        $profileTitle = $isCustomer ? __('Profil Pelanggan') : __('Profil Pembekal');
        $detailTitle = $isCustomer ? __('Butiran Pelanggan') : __('Butiran Pembekal');
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $profileTitle }}</h2>
        <a href="{{ url('/' . $type) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
            {{ __('Kembali') }}
        </a>
    </div>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 via-white to-white px-6 py-7 dark:border-gray-800 dark:from-brand-500/10 dark:via-gray-900 dark:to-gray-900 sm:px-8">
                <div class="flex flex-wrap items-center gap-5">
                    <div class="min-w-0">
                        <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $contact->company_name ?: ($contact->customer_name ?: '—') }}</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $contact->customer_name ?: __('Tiada nama pegawai diberikan') }}</p>
                    </div>
                    <a href="{{ url('/' . $type . '/' . $contact->customer_id . '/edit') }}" class="ms-auto inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                        {{ $isCustomer ? __('Edit Pelanggan') : __('Edit Pembekal') }}
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
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $detailTitle }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Maklumat hubungan dan rujukan') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['label' => 'Kod', 'value' => $contact->customer_code],
                        ['label' => 'Nama', 'value' => $contact->customer_name],
                        ['label' => 'Nama Syarikat', 'value' => $contact->company_name],
                        ['label' => 'Nombor Telefon', 'value' => $contact->phone_no],
                        ['label' => 'E-mel', 'value' => $contact->email],
                        ['label' => 'No. Rujukan', 'value' => $contact->reference_no],
                    ] as $item)
                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __($item['label']) }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-gray-800 dark:text-white/90">{{ $item['value'] ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Alamat') }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm font-semibold leading-6 text-gray-800 dark:text-white/90">{{ $contact->address ?: '—' }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
