@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <x-common.component-card>
        <x-tables.basic-tables.supplier-document-table :documents="$deliveryOrders" :type="$documentType" :createUrl="isset($createUrl) ? $createUrl : null" />
    </x-common.component-card>
@endsection
