@props(['number', 'backUrl', 'printUrl', 'downloadUrl' => null, 'label' => __('Pratonton PDF')])
<x-common.page-breadcrumb :pageTitle="__('Pratonton PDF')">
    <x-slot:actions><a href="{{ $backUrl }}" class="document-action">{{ __('Kembali') }}</a></x-slot:actions>
</x-common.page-breadcrumb>
<x-common.pdf-viewer :number="$number" :pdf-url="$printUrl" :download-url="$downloadUrl ?? ($printUrl.(str_contains($printUrl, '?') ? '&' : '?').'download=1')" :label="$label" />
