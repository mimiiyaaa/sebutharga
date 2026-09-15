@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pelanggan" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pelanggan">
            <a href="{{ route('contacts.create', ['type' => 'pelanggan']) }}" class="mb-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">+ Tambah Pelanggan</a>
            <x-tables.basic-tables.customer-table :contacts="$contacts" />
        </x-common.component-card>
    </div>
@endsection
