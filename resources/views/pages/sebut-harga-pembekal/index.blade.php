@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Sebut Harga Pembekal')" />
<x-common.component-card :title="__('Senarai Sebut Harga Pembekal')">
    @if (session('success'))<p class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>@endif
    <a href="{{ route('sebut-harga-pembekal.create') }}" class="mb-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">+ {{ __('Tambah Sebut Harga Pembekal') }}</a>
    <div class="overflow-x-auto custom-scrollbar"><table class="w-full min-w-[850px]">
        <thead><tr class="border-b border-gray-100 dark:border-gray-800">@foreach (['No. Sebut Harga', 'Pembekal', 'Tajuk', 'Person In Charge', 'Tindakan'] as $heading)<th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>@endforeach</tr></thead>
        <tbody>@forelse ($quotations as $quotation)<tr class="border-b border-gray-100 dark:border-gray-800">
            <td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $quotation->quotation_no_supplier }}</td><td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $quotation->company_name }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $quotation->quotation_title ?: '—' }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $quotation->person_in_charge ?: '—' }}</td><td class="whitespace-nowrap px-5 py-4"><div class="flex items-center gap-2"><a href="{{ route('sebut-harga-pembekal.edit', $quotation->supplier_quotation_id) }}" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white">{{ __('Lihat') }}</a><form method="POST" action="{{ route('sebut-harga-pembekal.delete', $quotation->supplier_quotation_id) }}" onsubmit="return confirm('{{ __('Padam rekod ini?') }}')">@csrf @method('DELETE')<button class="rounded-lg border border-error-200 p-2 text-error-600" title="{{ __('Padam') }}">🗑</button></form></div></td>
        </tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td></tr>@endforelse</tbody>
    </table></div>
</x-common.component-card>
@endsection
