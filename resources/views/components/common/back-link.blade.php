@props(['fallback'])
<a href="{{ $fallback }}" x-data @click="if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) { $event.preventDefault(); window.history.back(); }" {{ $attributes->class(['document-action']) }}>{{ __('Kembali') }}</a>
