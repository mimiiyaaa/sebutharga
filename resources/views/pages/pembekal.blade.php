@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pembekal" />

    <div class="space-y-6">
        <x-common.component-card title="Senarai Pembekal">
            <x-tables.basic-tables.customer-table :contacts="$contacts" />
        </x-common.component-card>
    </div>
@endsection
