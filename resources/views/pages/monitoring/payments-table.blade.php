<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
<table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
<thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach(['Invois', $tab === 'customer' ? 'Pelanggan' : 'Pembekal', 'Jumlah Invois', $tab === 'customer' ? 'Diterima' : 'Dibayar', 'Baki', 'Status', ...($tab === 'supplier' ? ['Baucar Bayaran'] : []), 'Tindakan'] as $label)<th class="px-4 py-4 text-start">{{ __($label) }}</th>@endforeach</tr></thead>
<tbody class="divide-y divide-gray-100 dark:divide-gray-800">
@forelse($rows as $row)
<tr>
<td class="px-4 py-4 font-medium">{{ $row['number'] }}<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</p></td><td class="px-4 py-4">{{ $row['party'] }}</td>
@foreach(['total','paid','balance'] as $amount)<td class="whitespace-nowrap px-4 py-4 tabular-nums">{{ $money($row[$amount]) }}</td>@endforeach
<td class="px-4 py-4"><span class="font-medium {{ $row['status'] === 'Selesai Dibayar' ? 'text-success-700 dark:text-success-400' : 'text-brand-600 dark:text-brand-300' }}">{{ __($row['status']) }}</span>
@if($tab === 'customer' && $row['balance'] > 0 && in_array($row['priority'], ['Sudah Lewat', 'Hampir Jatuh Tempoh']))
<p class="mt-1 text-xs {{ $row['priority'] === 'Sudah Lewat' ? 'text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">{{ __($row['priority']) }}@if($row['due_date']) | {{ \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') }}@endif</p>
@endif
</td>
@if($tab === 'supplier')
<td class="min-w-48 px-4 py-4">
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $row['linked_vouchers']->isNotEmpty() ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400' }}">{{ __($row['linked_vouchers']->isNotEmpty() ? 'Sudah Ada Baucar' : 'Belum Ada Baucar') }}</span>
    @foreach($row['linked_vouchers'] as $voucher)
        <div class="mt-3">
            <p class="font-medium">{{ $voucher->voucher_no }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $money($voucher->amount) }} | {{ __($voucher->supplier_payment_status === 'paid' ? 'Sudah Bayar' : 'Belum Bayar') }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') }}</p>
        </div>
    @endforeach
</td>
@endif
<td class="px-4 py-4"><a class="document-action" href="{{ $tab === 'customer' ? $row['url'] : route('invoice.supplier.show', $row['id']) }}">{{ __('Lihat') }}</a>@if($tab === 'supplier' && $row['balance'] > 0)<a class="document-action mt-2" href="{{ route('baucar-bayaran.supplier.create', ['invoice_id' => $row['id']]) }}">{{ __('Rekod Bayaran') }}</a>@endif</td>
</tr>
@empty<tr><td colspan="{{ $tab === 'supplier' ? 8 : 7 }}" class="p-8 text-center">{{ __('Tiada invois untuk pilihan ini.') }}</td></tr>@endforelse
</tbody></table></div>
