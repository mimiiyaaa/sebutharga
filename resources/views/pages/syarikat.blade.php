@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Syarikat')" />
    <x-common.component-card :title="__('Senarai Syarikat')">
        <x-slot:header>
            <a href="{{ route('companies.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                + {{ __('Tambah Syarikat') }}
            </a>
        </x-slot:header>
        <x-tables.basic-tables.company-table :companies="$companies" />
    </x-common.component-card>
@endsection
