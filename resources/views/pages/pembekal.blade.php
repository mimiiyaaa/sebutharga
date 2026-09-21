@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pembekal" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pembekal">
            <x-slot:header>
                <a href="{{ route('contacts.create', ['type' => 'pembekal']) }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                    + Tambah Pembekal
                </a>
            </x-slot:header>
            <x-tables.basic-tables.customer-table :contacts="$contacts" />
        </x-common.component-card>
    </div>
@endsection
