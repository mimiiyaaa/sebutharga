@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="__('Invois Kepada Pelanggan')">
    <x-slot:referenceActions><a href="{{ route('invoice.customer.form') }}" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Cipta Invois') }}</a></x-slot:referenceActions>
    <x-common.document-card :title="__('Senarai Invois')">
        <div class="overflow-x-auto"><table class="w-full text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-gray-50 text-start dark:bg-gray-800"><tr>@foreach(['No. Dokumen','Pelanggan / Syarikat','Jumlah (RM)','Tindakan'] as $label)<th class="px-4 py-3 text-start">{{ __($label) }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($invoices as $invoice)
                <tr><td class="px-4 py-4 font-medium"><span class="block">{{ $invoice->invoice_no }}</span><span class="mt-1 block text-xs font-normal text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span></td><td class="px-4 py-4">{{ json_decode($invoice->document_data ?? '{}', true)['customer_name'] ?? '—' }}</td><td class="px-4 py-4 tabular-nums">{{ number_format($invoice->total_amount,2) }}</td><td class="px-4 py-4"><a href="{{ route('invoice.customer.preview',$invoice->invoice_id) }}" class="font-medium text-brand-600 dark:text-brand-400">{{ __('Pratonton PDF') }}</a></td></tr>
            @empty<tr><td colspan="4" class="py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada invois direkodkan.') }}</td></tr>@endforelse</tbody>
        </table></div>
    </x-common.document-card>
</x-common.document-workspace>
@endsection
