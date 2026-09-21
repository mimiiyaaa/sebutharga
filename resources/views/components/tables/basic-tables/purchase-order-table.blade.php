@props(['orders' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="mb-4 flex flex-col gap-4 px-6 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            {{ __('Senarai Pesanan Belian (PO)') }}
        </h3>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('purchase-order.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">
                {{ __('+ Tambah PO') }}
            </a>
            <button type="button" disabled title="{{ __('Akan datang') }}"
                class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 opacity-50 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <svg class="h-5 w-5 stroke-current" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 6h14M3 14h14M12 3v6M8 11v6" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                {{ __('Tapis') }}
            </button>
            <button type="button" disabled title="{{ __('Akan datang') }}"
                class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 opacity-50 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                {{ __('Lihat semua') }}
            </button>
        </div>
    </div>

    <div class="max-w-full overflow-x-auto">
        <table class="w-full">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    @foreach (['No. PO', 'Sebut Harga', 'Pembekal', 'Tarikh', 'Jumlah (RM)', 'Tindakan'] as $heading)
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __($heading) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-b border-gray-100 font-outfit text-theme-sm text-gray-600 dark:border-white/[0.05] dark:text-gray-300">
                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-800 dark:text-white/90">{{ $order->po_no }}</td>
                    <td class="px-6 py-4">{{ $order->quotation_reference_no ?: $order->quotation_detail_id }}</td>
                    <td class="min-w-52 px-6 py-4 leading-relaxed">{{ $order->company_name ?: '—' }}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{ \Carbon\Carbon::parse($order->po_date)->format('d/m/Y') }}</td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium tabular-nums">{{ number_format($order->net_amount,2) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('purchase-order.show',$order->purchase_order_id) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">{{ __('Lihat') }}</a>
                            <a href="{{ route('purchase-order.edit',$order->purchase_order_id) }}" class="rounded-lg bg-brand-500 px-3 py-2 text-theme-xs font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-600">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('purchase-order.delete',$order->purchase_order_id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam PO ini?' })">
                                @csrf @method('DELETE')
                                <button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex rounded-lg border border-error-200 p-2 text-error-600 hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10">
                                    <svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5m4-5v5" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Tiada pesanan belian untuk dipaparkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
