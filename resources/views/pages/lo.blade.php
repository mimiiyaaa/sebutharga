@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="LO" />

    <x-common.component-card>
        <x-tables.basic-tables.lo-table :documents="$loDocuments" />
    </x-common.component-card>
@endsection
