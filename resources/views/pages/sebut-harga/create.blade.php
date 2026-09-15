@extends('layouts.app')
@section('content')
    <x-common.document-workspace :title="__('Tambah Sebut Harga')" :subtitle="__('Lengkapkan maklumat pelanggan, item dan terma dokumen.')">
        <x-sebut-harga.create-form :customers="$customers" />
    </x-common.document-workspace>
@endsection
