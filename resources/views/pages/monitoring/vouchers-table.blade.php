<section class="space-y-3">
<h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Senarai Baucar Pembekal') }}</h3>
<p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Baucar dan status bayaran kepada pembekal sehingga hari ini.') }}</p>
<div class="grid gap-4 sm:grid-cols-2">
    <x-dashboard.monitoring-card label="Baucar Dah Bayar" :value="$vouchers->where('supplier_payment_status', 'paid')->count()" :amounts="['Amaun Baucar' => $vouchers->where('supplier_payment_status', 'paid')->sum('total_amount')]" tone="success" />
    <x-dashboard.monitoring-card label="Baucar Belum Bayar" :value="$vouchers->where('supplier_payment_status', 'unpaid')->count()" :amounts="['Amaun Baucar' => $vouchers->where('supplier_payment_status', 'unpaid')->sum('total_amount')]" tone="warning" />
</div>
<p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Hanya baucar Dah Bayar yang merujuk invois mengurangkan baki invois. Baucar Standalone direkod berasingan tanpa menjejaskan baki invois.') }}</p>
@if($vouchers->sum('unallocated') > 0)
<x-dashboard.monitoring-card label="Baucar Perlu Dilengkapkan" :value="$money($vouchers->sum('unallocated'))" tone="purple" />
<p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Baucar lama ini belum mempunyai rujukan invois yang lengkap. Tekan Lengkapkan Rekod dan pilih invois dalam borang baucar.') }}</p>
@endif
<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"><table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
<thead class="bg-gray-50 dark:bg-gray-800"><tr>@foreach(['No. Baucar','Pembekal','Amaun Baucar','Status Bayaran','Amaun Tanpa Rujukan Invois','Tindakan'] as $label)<th class="px-4 py-4 text-start">{{ __($label) }}</th>@endforeach</tr></thead>
<tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($vouchers as $voucher)<tr><td class="px-4 py-4">{{ $voucher->voucher_no }}</td><td class="px-4 py-4">{{ $voucher->payee_name }}</td><td class="px-4 py-4">{{ $money($voucher->total_amount) }}</td><td class="px-4 py-4"><a href="{{ route('baucar-bayaran.show', $voucher->id) }}" class="font-medium {{ $voucher->supplier_payment_status === 'paid' ? 'text-success-700 dark:text-success-400' : 'text-warning-700 dark:text-warning-400' }}">{{ __($voucher->supplier_payment_status === 'paid' ? 'Dah Bayar' : 'Belum Bayar') }}</a></td><td class="px-4 py-4">{{ $voucher->standalone ? __('Baucar Standalone') : $money($voucher->unallocated) }}</td><td class="px-4 py-4"><a class="document-action" href="{{ route('baucar-bayaran.supplier.edit', $voucher->id) }}">{{ __($voucher->unallocated > 0 ? 'Lengkapkan Rekod' : 'Edit Bayaran') }}</a></td></tr>@empty<tr><td colspan="6" class="p-8 text-center">{{ __('Tiada baucar pembekal.') }}</td></tr>@endforelse</tbody>
</table></div></section>
