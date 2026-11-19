@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <x-tables.basic-tables.supplier-document-table :documents="$invoices" :type="$documentType" :createUrl="isset($createUrl) ? $createUrl : null" :party-label="$partyLabel ?? __('Pembekal')" :invoice-titles="true" :customer-quotation-columns="$customerQuotationColumns ?? false" />
@endsection
