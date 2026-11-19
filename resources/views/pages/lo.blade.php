@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Senarai Local Order" />

    <x-tables.basic-tables.lo-table :documents="$loDocuments" />
@endsection
