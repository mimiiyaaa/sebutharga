@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Sebut Harga" />

    <x-common.component-card title="Maklumat Sebut Harga">
        <p>No. Sebut Harga: {{ $quotationId }}</p>
    </x-common.component-card>
@endsection