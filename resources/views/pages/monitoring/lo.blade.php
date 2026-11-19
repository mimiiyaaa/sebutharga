@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Rujukan PO kepada LO')" />
<form method="POST" action="{{ route('monitoring.lo.save', $lo->lo_inden_id) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
@csrf @method('PUT')
<h2 class="border-s-4 border-brand-500 ps-4 text-xl font-semibold">{{ $lo->lo_inden_no }}</h2>
<p>{{ __('Pilih PO yang disediakan untuk LO ini. DO dan invois mengikut rujukan LO pada dokumen masing-masing.') }}</p>
@foreach($errors->all() as $error)<p class="text-error-600 dark:text-error-400">{{ $error }}</p>@endforeach
<input type="hidden" name="selection_present" value="1">
@forelse($orders as $order)
<label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700"><input type="checkbox" name="orders[]" value="{{ $order->purchase_order_id }}" @checked(old('selection_present') ? in_array($order->purchase_order_id, old('orders', [])) : (int)$order->lo_inden_id === (int)$lo->lo_inden_id) class="h-5 w-5">{{ $order->po_no }} | {{ \Carbon\Carbon::parse($order->po_date)->format('d/m/Y') }}</label>
@empty<p>{{ __('Tiada PO tersedia untuk dipilih.') }}</p>@endforelse
<div class="flex gap-3"><button class="rounded-lg bg-brand-500 px-5 py-3 text-white dark:bg-brand-500">{{ __('Simpan') }}</button><a class="document-action" href="{{ route('monitoring', ['tab' => 'lo']) }}">{{ __('Kembali') }}</a></div>
</form>
@endsection
