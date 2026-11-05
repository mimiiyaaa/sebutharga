@extends('layouts.app')
@section('content')
<x-common.document-preview :number="$order->po_no" :back-url="route('purchase-order.show', $order->purchase_order_id)" :print-url="route('purchase-order.pdf', $order->purchase_order_id)" :label="__('Pesanan Belian (PO)')" />
@endsection
