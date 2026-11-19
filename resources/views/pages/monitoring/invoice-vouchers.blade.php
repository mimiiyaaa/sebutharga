@extends('layouts.app')
@section('content')
<x-common.document-workspace :title="__('Senarai Baucar Pembekal')" :reference="$invoice->document_no" :subtitle="$invoice->supplier_name">
    <x-slot:referenceActions><x-common.back-link :fallback="route('monitoring', ['tab' => 'supplier'])" /></x-slot:referenceActions>
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach(['No. Baucar','Tarikh Baucar','Cara Bayaran','Amaun Bayaran (RM)','Status Bayaran Baucar','Tindakan'] as $heading)<th class="px-5 py-4 text-start">{{ __($heading) }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($vouchers as $voucher)
                <tr>
                    <td class="px-5 py-4 font-semibold">{{ $voucher->voucher_no }}</td>
                    <td class="px-5 py-4">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</td>
                    <td class="px-5 py-4">{{ $voucher->payment_method ?: '—' }}</td>
                    <td class="px-5 py-4 tabular-nums">RM {{ number_format($voucher->amount, 2) }}</td>
                    <td class="px-5 py-4">{{ __($voucher->supplier_payment_status === 'paid' ? 'Dah Bayar' : 'Belum Bayar') }}</td>
                    <td class="px-5 py-4"><a class="document-action" href="{{ route('baucar-bayaran.supplier.preview', $voucher->id) }}">{{ __('Lihat PDF') }}</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center">{{ __('Tiada baucar pembekal.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-common.document-workspace>
@endsection
