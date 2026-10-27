@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Pemantauan')" />
    <x-dashboard.payment-monitoring-preview />
@endsection
