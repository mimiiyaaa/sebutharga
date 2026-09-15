@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Pesanan Belian (PO)')" />

    <div class="space-y-6">
        @if(session('success'))
            <p role="status" class="rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>
        @endif
        <x-common.component-card :title="__('Pesanan Belian (PO)')">
            <x-tables.basic-tables.purchase-order-table :orders="$orders" />
        </x-common.component-card>
    </div>
@endsection
