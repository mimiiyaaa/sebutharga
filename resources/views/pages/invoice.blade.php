@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <x-common.component-card>
        <x-tables.basic-tables.supplier-document-table :documents="$invoices" :type="$documentType" :createUrl="isset($createUrl) ? $createUrl : null" :party-label="$partyLabel ?? __('Pembekal')" />
    </x-common.component-card>
@endsection
