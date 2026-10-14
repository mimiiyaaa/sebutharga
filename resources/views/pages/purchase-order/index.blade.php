@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Senarai Pesanan Belian (PO)')" />

    <div class="space-y-6">
        <x-tables.basic-tables.purchase-order-table :orders="$orders" />
    </div>
@endsection
