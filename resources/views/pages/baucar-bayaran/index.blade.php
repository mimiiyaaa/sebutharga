@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="font-outfit" x-data="{ search: '' }">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-wrap items-center gap-2 px-6">
                <input x-model="search" type="search" placeholder="{{ __('Cari no. baucar atau penerima') }}" aria-label="{{ __('Cari no. baucar atau penerima') }}" class="h-11 min-w-60 flex-1 rounded-lg border border-gray-200 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <a href="{{ route('baucar-bayaran.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">+ {{ __('Tambah') }}</a>
            </div>
            <div class="min-w-0 max-w-full overflow-x-auto px-6">
                <table class="document-list-table w-full text-theme-sm font-medium leading-6 text-gray-700 dark:text-gray-300">
                    <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900"><tr>@foreach (['No. Baucar', 'Tarikh', 'Penerima', 'Keterangan', 'Jumlah (RM)', 'Tindakan'] as $heading)<th class="px-4 py-3 font-semibold text-gray-500 sm:px-6 {{ $heading === 'Jumlah (RM)' ? 'text-end' : ($heading === 'Tindakan' ? 'text-center' : 'text-start') }}">{{ __($heading) }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($documents as $document)
                            <tr x-show="!search || @js($document->voucher_no . ' ' . $document->payee_name . ' ' . $document->description).toLowerCase().includes(search.toLowerCase())"><td class="p-4 font-medium">{{ $document->voucher_no }}</td><td class="p-4">{{ \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y') }}</td><td class="p-4">{{ $document->payee_name }}</td><td class="max-w-sm whitespace-pre-line break-words p-4">{{ $document->description }}</td><td class="p-4 text-end tabular-nums">{{ number_format($document->total_amount, 2) }}</td><td class="p-4"><div class="document-actions"><a href="{{ route('baucar-bayaran.show', $document->id) }}" class="document-action">{{ __('Lihat') }}</a><form method="POST" action="{{ route('baucar-bayaran.delete', $document->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail baucar ini?')) })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="document-action document-action-delete"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button></form></div></td></tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada baucar bayaran untuk dipaparkan.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
