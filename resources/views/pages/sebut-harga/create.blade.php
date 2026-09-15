@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah Sebut Harga" />

    <x-sebut-harga.create-form :customers="$customers" />
@endsection
