@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Syarikat')" />
    <x-common.component-card :title="__('Senarai Syarikat')">
        <a href="{{ route('companies.create') }}" class="mb-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">+ {{ __('Tambah Syarikat') }}</a>
        <x-tables.basic-tables.company-table :companies="$companies" />
    </x-common.component-card>
@endsection
