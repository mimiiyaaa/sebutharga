@extends('layouts.app')
@section('content')
@if($previewHtml || !str_starts_with($document->file_mime, 'image/'))
<x-common.document-preview :number="$document->voucher_no" :back-url="route('baucar-bayaran.show', $document->id)" :print-url="route('baucar-bayaran.supplier.pdf', $document->id)" :download-url="route('baucar-bayaran.file', [$document->id, 'download' => 1])" :label="__('Baucar Bayaran')" />
@else
<div class="mb-5 flex justify-end gap-2"><a href="{{ route('baucar-bayaran.show', $document->id) }}" class="document-action">{{ __('Kembali') }}</a><a href="{{ route('baucar-bayaran.file', [$document->id, 'download' => 1]) }}" class="document-action">{{ __('Muat Turun Dokumen Asal') }}</a></div>
<img src="{{ route('baucar-bayaran.file', $document->id) }}" alt="{{ $document->voucher_no }}" class="mx-auto block h-auto max-w-full">
@endif
@endsection
