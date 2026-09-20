@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sebut Harga" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Sebut Harga">
            <x-tables.basic-tables.sebutharga-table :quotations="$quotations" />
        </x-common.component-card>
    </div>
@endsection
