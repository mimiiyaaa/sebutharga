@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Pesanan Belian (PO)')" />

    <div class="space-y-6">
        <x-common.component-card :title="__('Pesanan Belian (PO)')">
            <x-tables.basic-tables.purchase-order-table />
        </x-common.component-card>
    </div>
@endsection
