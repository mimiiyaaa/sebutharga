@props(['invoice', 'payments'])
@php($summary = \App\Services\InvoicePayments::summary($invoice->invoice_id, $invoice->total_amount))
<x-common.document-card :title="__('Bayaran Pelanggan')">
    <dl class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (['Jumlah Invois' => $invoice->total_amount, 'Sudah Dibayar' => $summary['paid_amount'], 'Baki' => $summary['balance_amount']] as $label => $amount)
            <div><dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __($label) }}</dt><dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format($amount, 2) }}</dd></div>
        @endforeach
        <div><dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Status Bayaran') }}</dt><dd class="mt-1 font-semibold text-brand-600 dark:text-brand-400">{{ $summary['payment_status'] }}</dd></div>
    </dl>
    @if ($summary['balance_amount'] > 0 && $summary['payment_status'] !== __('Perlu Semakan'))
        <a href="{{ route('baucar-bayaran.form', ['invoice_ids' => [$invoice->invoice_id]]) }}" class="document-action document-action-primary">{{ __('Rekod Bayaran') }}</a>
    @endif
    @if ($summary['payment_status'] === __('Perlu Semakan'))
        <p class="text-sm text-warning-700 dark:text-warning-400">{{ __('Semak amaun baucar lama untuk invois ini dahulu.') }}</p>
    @endif
    <h3 class="mb-3 mt-5 font-semibold text-gray-800 dark:text-white/90">{{ __('Sejarah Bayaran') }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-start text-theme-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-800"><tr>
                @foreach (['Tarikh', 'No. Baucar', 'Cara Bayaran', 'Bayaran Kali Ini (RM)', 'Tindakan'] as $label)<th class="px-3 py-3 text-start">{{ __($label) }}</th>@endforeach
            </tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-3 py-3">{{ \Carbon\Carbon::parse($payment->voucher_date)->format('d/m/Y') }}</td>
                        <td class="px-3 py-3">{{ $payment->voucher_no }}</td>
                        <td class="px-3 py-3">{{ $payment->payment_method }}</td>
                        <td class="px-3 py-3 tabular-nums">{{ $payment->amount === null ? __('Perlu Semakan') : number_format($payment->amount, 2) }}</td>
                        <td class="px-3 py-3"><a class="document-action" href="{{ route('baucar-bayaran.show', $payment->id) }}">{{ __('Lihat Baucar') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('Belum ada bayaran direkodkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-common.document-card>
