@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pelanggan" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pelanggan">
            <x-tables.basic-tables.customer-table />
        </x-common.component-card>
    </div>
@endsection