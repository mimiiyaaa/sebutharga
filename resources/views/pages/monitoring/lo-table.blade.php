<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
<table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
    <thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach(['No. LO','Pelanggan','Tarikh','PO','DO','Invois','Tindakan'] as $label)<th class="px-4 py-4 text-start">{{ __($label) }}</th>@endforeach</tr></thead>
    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
    @forelse($loRecords as $lo)
    <tr><td class="px-4 py-4"><a class="text-brand-500" href="{{ route('lo.show', $lo->lo_inden_id) }}">{{ $lo->lo_inden_no }}</a></td><td class="px-4 py-4">{{ $lo->company_name }}</td><td class="px-4 py-4">{{ \Carbon\Carbon::parse($lo->lo_inden_date)->format('d/m/Y') }}</td>
    @foreach(['pos' => ['po_no','purchase_order_id','purchase-order.show'], 'dos' => ['do_no','delivery_order_id','delivery-order.customer.show'], 'invoices' => ['invoice_no','invoice_id','invoice.customer.show']] as $key => [$number,$id,$route])
    <td class="px-4 py-4">@forelse($lo->$key as $document)<a class="mb-1 block font-medium text-success-700 dark:text-success-400" href="{{ route($route, $document->$id) }}">{{ $document->$number }}</a>@empty<span class="rounded-full bg-warning-50 px-2 py-1 text-xs text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Belum disediakan') }}</span>@endforelse</td>
    @endforeach
    <td class="px-4 py-4"><a class="document-action" href="{{ route('monitoring.lo', $lo->lo_inden_id) }}">{{ __('Pilih PO') }}</a></td></tr>
    @empty<tr><td colspan="7" class="p-8 text-center">{{ __('Tiada LO untuk pilihan ini.') }}</td></tr>@endforelse
    </tbody>
</table></div>
