@extends('layouts.app')
@section('content')
    <x-common.document-preview
        :number="$invoice->invoice_no"
        :back-url="route('invoice.customer.show', $invoice->invoice_id)"
        :print-url="route('invoice.customer.pdf', $invoice->invoice_id)"
        :html="view('pages.invoice.pdf', compact('invoice', 'items', 'document'))->render()"
    />
@endsection
