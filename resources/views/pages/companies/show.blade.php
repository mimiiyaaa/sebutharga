@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Butiran Syarikat')" /><div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
@foreach (['nama_syarikat' => 'Nama Syarikat', 'no_telefon' => 'No. Telefon', 'emel' => 'E-mel', 'person_in_charge' => 'Person In Charge', 'alamat_syarikat' => 'Alamat Syarikat'] as $field => $label)<div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __($label) }}</p><p class="mt-1 whitespace-pre-line text-base text-gray-800 dark:text-white/90">{{ $company->$field ?: '—' }}</p></div>@endforeach
<div class="flex gap-3"><a href="{{ route('syarikat') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:text-gray-300">← {{ __('Kembali') }}</a><a href="{{ route('companies.edit', $company->company_id) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">{{ __('Edit') }}</a></div></div>
@endsection
