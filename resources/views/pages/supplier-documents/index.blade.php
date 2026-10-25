@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />
<div class="font-outfit" x-data="{ search:'', date:'', matches(number, supplier, source, date, title) { const q=this.search.toLowerCase(); return (!q || `${number} ${supplier} ${source} ${title || ''}`.toLowerCase().includes(q)) && (!this.date || this.date===date); } }">
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]"><div class="mb-4 px-6"><div class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50"><input x-model="search" type="search" placeholder="{{ __('Cari nombor, pembekal atau sumber') }}" aria-label="{{ __('Cari nombor, pembekal atau sumber') }}" class="h-11 min-w-40 flex-1 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300"><input x-model="date" type="date" aria-label="Tarikh" class="h-11 w-44 shrink-0 rounded-lg border-0 bg-white px-3 text-theme-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300"><button type="button" @click="search='';date=''" class="inline-flex h-11 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">{{ __('See all') }}</button><a href="{{ route($prefix.'.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-theme-sm font-medium text-white transition hover:bg-brand-600">{{ __('+ Tambah') }}</a></div></div>
<div class="min-w-0 max-w-full px-6">
    <table class="document-list-table w-full text-theme-sm font-medium leading-6 text-gray-700 dark:text-gray-300">
        <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
            <tr>
                <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6">{{ __('No. Dokumen') }}</th>
                @if ($isInvoice)
                    <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6">{{ __('Tajuk Invois') }}</th>
                @endif
                @foreach (['Pembekal', 'Sumber', 'Fail Asal'] as $heading)
                    <th class="px-4 py-3 text-start font-semibold text-gray-500 sm:px-6">{{ __($heading) }}</th>
                @endforeach
                @if ($isInvoice)
                    <th class="p-4 text-end">{{ __('Jumlah (RM)') }}</th>
                @endif
                <th class="p-4 text-center">{{ __('Tindakan') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($documents as $document)
                <tr x-show="matches(@js($document->document_no), @js($document->supplier_name), @js($document->source), @js($document->document_date), @js($document->document_title ?? ''))">
                    <td data-label="{{ __('No. Dokumen') }}" class="p-4 font-medium">
                        <span class="block">{{ $document->document_no }}</span>
                        <span class="mt-1 block text-xs font-normal text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($document->document_date)->format('d/m/Y') }}</span>
                    </td>
                    @if ($isInvoice)
                        <td data-label="{{ __('Tajuk Invois') }}" class="max-w-xs break-words p-4">{{ $document->document_title ?: '—' }}</td>
                    @endif
                    <td data-label="{{ __('Pembekal') }}" class="p-4">{{ $document->supplier_name }}</td>
                    <td data-label="{{ __('Sumber') }}" class="p-4">
                        {{ __($document->source) }}
                        @if ($document->source_details)
                            <p class="mt-1 max-w-xs break-words text-theme-xs text-gray-500 dark:text-gray-400">{{ $document->source_details }}</p>
                        @endif
                    </td>
                    <td data-label="{{ __('Fail Asal') }}" class="max-w-xs break-words p-4">{{ $document->file_name }}</td>
                    @if ($isInvoice)
                        <td data-label="{{ __('Jumlah') }}" class="p-4 text-end tabular-nums">{{ number_format($document->total_amount, 2) }}</td>
                    @endif
                    <td data-label="{{ __('Tindakan') }}" class="p-4">
                        <div class="document-actions">
                            <a href="{{ route($prefix . '.show', $document->id) }}" class="document-action">{{ __('Lihat') }}</a>
                            <form method="POST" action="{{ route($prefix . '.delete', $document->id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: @js(__('Padam rekod dan fail dokumen ini?')) })">
                                @csrf
                                @method('DELETE')
                                <button title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" type="submit" class="document-action document-action-delete">
                                    <svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr x-cloak x-show="!@js($documents->map(fn ($d) => ['number' => $d->document_no, 'supplier' => $d->supplier_name, 'source' => $d->source, 'date' => $d->document_date, 'title' => $d->document_title ?? ''])->values()).some(row => matches(row.number, row.supplier, row.source, row.date, row.title))">
                <td colspan="{{ $isInvoice ? 7 : 5 }}" class="p-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td>
            </tr>
        </tbody>
    </table>
</div>
</tbody></table></div></div></div>
@endsection
