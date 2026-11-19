@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__($title)">
        <x-slot:referenceActions>
            <a href="{{ route($backRoute) }}" class="inline-flex h-9 items-center justify-center px-3 py-1.5 text-theme-xs rounded-lg border border-gray-300 bg-white font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </x-slot:referenceActions>
        <x-supplier-document.editor :document-type="$documentType" :document-number-label="$documentNumberLabel" :invoice="request()->routeIs('invoice.*')" />
    </x-common.document-workspace>
@endsection
