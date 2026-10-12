@extends('layouts.app')
@section('content')
    <x-common.document-preview
        :number="$order->do_no"
        :back-url="route('delivery-order.customer.show', $order->delivery_order_id)"
        :print-url="route('delivery-order.customer.pdf', $order->delivery_order_id)"
        :html="view('pages.delivery-order.pdf', compact('order', 'items', 'document'))->render()"
    />
@endsection
