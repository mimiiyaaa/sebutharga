@extends('layouts.app')

@section('content')
    @php
        $isCustomer = $type === 'pelanggan';
        $profileTitle = $isCustomer ? __('Profil Pelanggan') : __('Profil Pembekal');
        $detailTitle = $isCustomer ? __('Butiran Pelanggan') : __('Butiran Pembekal');
    @endphp

    <x-common.document-workspace :title="$profileTitle">
        <x-slot:referenceActions>
            <a href="{{ url('/' . $type) }}" class="document-action">{{ __('Kembali') }}</a>
            <a href="{{ url('/' . $type . '/' . $contact->customer_id . '/edit') }}" class="document-action document-action-primary">{{ $isCustomer ? __('Edit Pelanggan') : __('Edit Pembekal') }}</a>
        </x-slot:referenceActions>
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-6 dark:border-gray-800">
                <x-common.document-heading :label="$detailTitle" :number="$contact->customer_code" :subtitle="$contact->company_name ?: ($contact->customer_name ?: '—')" />
            </div>
        </section>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 bg-white px-6 py-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center gap-5">
                    <div class="min-w-0 border-s-4 border-brand-500 ps-4 dark:border-brand-400">
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
    </x-common.document-workspace>
@endsection
