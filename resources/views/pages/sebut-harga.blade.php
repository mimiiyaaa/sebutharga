@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sebut Harga" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Sebut Harga">
            <x-slot:header>
                <a href="{{ route('sebut-harga.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">
                    + {{ __('Tambah Sebut Harga') }}
                </a>
            </x-slot:header>
            <x-tables.basic-tables.sebutharga-table :quotations="$quotations" />
        </x-common.component-card>
    </div>
@endsection
