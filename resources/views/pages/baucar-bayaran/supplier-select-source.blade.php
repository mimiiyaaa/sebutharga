@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="$title" :subtitle="__('Pilih cara menyediakan baucar bayaran kepada pembekal.')">
<x-slot:referenceActions><a href="{{ route('baucar-bayaran.supplier') }}" class="document-action">{{ __('Kembali') }}</a></x-slot:referenceActions>
<div class="grid gap-6 md:grid-cols-2">
@foreach(['standalone' => ['Baucar Standalone', 'Sediakan baucar untuk upah, perkhidmatan atau bayaran lain tanpa rujukan invois.', 'Tanpa Invois'], 'invoice' => ['Daripada Invois', 'Pilih invois pembekal dan rekod bayaran penuh atau sebahagian.', 'Dengan Invois']] as $source => [$label, $description, $badge])
<a href="{{ route('baucar-bayaran.supplier.form', ['source' => $source]) }}" class="block rounded-2xl border border-gray-200 bg-white p-7 shadow-theme-xs transition hover:border-brand-500 focus-visible:outline-brand-500 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-400">
<span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __($badge) }}</span>
<h2 class="mt-5 text-xl font-semibold text-gray-900 dark:text-white/90">{{ __($label) }}</h2>
<p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ __($description) }}</p>
<span class="mt-6 inline-flex items-center gap-2 font-medium text-brand-600 dark:text-brand-300">{{ __('Teruskan') }}<span aria-hidden="true" class="rtl:rotate-180">&rarr;</span></span>
</a>
@endforeach
</div>
</x-common.document-workspace>
@endsection
