@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Local Order" />

    <x-common.component-card>
        <x-tables.basic-tables.lo-table :documents="$loDocuments" />
    </x-common.component-card>
@endsection
