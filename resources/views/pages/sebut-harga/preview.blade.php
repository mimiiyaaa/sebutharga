@extends('layouts.app')
@section('content')
<x-common.document-preview
    :number="$quotation->quotation_no"
    :back-url="request('from') === 'final' && request('via') !== 'detail' ? route('sebut-harga.final') : route('sebut-harga.edit', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id, 'from' => request('from') === 'final' ? 'final' : 'draft'])"
    :print-url="route('sebut-harga.pdf', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id])"
    :label="__('Sebut Harga')"
/>
@endsection
