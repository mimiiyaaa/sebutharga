@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Tambah PO')" :subtitle="__('Lengkapkan maklumat pembekal, penghantaran dan item pesanan.')">
    <x-purchase-order.create-form :suppliers="$suppliers" :quotation="$quotation" :nextPoNo="$nextPoNo" />
    </x-common.document-workspace>
@endsection
