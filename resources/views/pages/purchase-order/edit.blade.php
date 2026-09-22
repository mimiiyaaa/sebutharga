@extends('layouts.app')
@section('content')
    <x-common.document-workspace :title="__('Edit').' '.$order->po_no">
    <x-purchase-order.create-form :suppliers="$suppliers" :quotation="$order" :nextPoNo="$order->po_no" :order="$order" :initialItems="$items" :return-to-detail="$returnToDetail" />
    </x-common.document-workspace>
@endsection
