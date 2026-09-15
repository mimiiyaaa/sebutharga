@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Tambah PO')" />
    <x-purchase-order.create-form :suppliers="$suppliers" :customers="$customers" :quotation="$quotation" />
@endsection
