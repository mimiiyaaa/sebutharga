@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pelanggan" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pelanggan">
            <x-slot:header>
                <a href="{{ route('contacts.create', ['type' => 'pelanggan']) }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                    + Tambah Pelanggan
                </a>
            </x-slot:header>
            <x-tables.basic-tables.customer-table :contacts="$contacts" />
        </x-common.component-card>
    </div>
@endsection
