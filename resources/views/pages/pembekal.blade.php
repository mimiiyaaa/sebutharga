@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pembekal" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pembekal">
            <a href="{{ route('contacts.create', ['type' => 'pembekal']) }}" class="mb-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">+ Tambah Pembekal</a>
            <x-tables.basic-tables.customer-table :contacts="$contacts" />
        </x-common.component-card>
    </div>
@endsection
