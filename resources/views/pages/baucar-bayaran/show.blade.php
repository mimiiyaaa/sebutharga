@extends('layouts.app')

@section('content')
    <x-common.document-workspace :title="__('Lihat Baucar Bayaran')" :reference="$document->voucher_no">
        <x-slot:referenceActions><a href="{{ route('baucar-bayaran.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a></x-slot:referenceActions>
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <x-common.document-heading :label="__('Maklumat Baucar')" :number="$document->voucher_no" :subtitle="$document->payee_name" />
    </section>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            <div class="space-y-6 lg:order-2">
                <x-common.document-card :title="__('Maklumat Baucar')">
                    <dl class="grid grid-cols-1 gap-5 text-sm md:grid-cols-2">
                        @foreach ([__('No. Baucar') => $document->voucher_no, __('Tarikh') => \Carbon\Carbon::parse($document->voucher_date)->format('d/m/Y'), __('Diterima Daripada') => $document->payee_name, __('Cara Bayaran') => $document->payment_method, __('Jumlah Dibayar (RM)') => number_format($document->total_amount, 2), __('Keterangan') => $document->description] as $label => $value)
                            <div class="{{ $label === __('Keterangan') ? 'md:col-span-2' : '' }}"><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium text-gray-800 dark:text-gray-200">{{ $value ?: '—' }}</dd></div>
                        @endforeach
                    </dl>
                </x-common.document-card>
                @if ($invoices->isNotEmpty())
                    <x-common.document-card :title="__('Invoice Pelanggan Dipilih')" :desc="__('Invoice yang dijadikan asas baucar bayaran ini.')">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">{{ __('No. Invoice') }}</th>
                                <th class="px-4 py-3">{{ __('Tajuk Invoice') }}</th>
                                <th class="px-4 py-3">{{ __('Pelanggan') }}</th>
                                <th class="px-4 py-3">{{ __('Tarikh') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Bayaran Kali Ini (RM)') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $invoice->document_no }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->document_title ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->customer_name ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->document_date ? \Carbon\Carbon::parse($invoice->document_date)->format('d/m/Y') : '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-800 dark:text-gray-200">{{ $invoice->amount === null ? __('Perlu Semakan') : 'RM '.number_format((float) $invoice->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">{{ __('Jumlah Keseluruhan') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-brand-600 dark:text-brand-400">RM {{ number_format((float) $invoices->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                    </x-common.document-card>
                    @if ($invoices->every(fn ($row) => $row->amount === null))
                    <x-common.document-card :title="__('Semak Bayaran Baucar Lama')" :desc="__('Amaun baucar lama belum disahkan. Isi amaun sebenar berdasarkan dokumen sebelum dikira sebagai bayaran.')">
                        <form method="POST" action="{{ route('baucar-bayaran.reconcile', $document->id) }}" class="space-y-4" x-data="{ total: '0.00', updateTotal(form) { const cents = Array.from(form.querySelectorAll('[data-payment-allocation]')).reduce((sum, input) => sum + Math.round((Number(input.value) || 0) * 100), 0); this.total = (cents / 100).toFixed(2); } }" x-init="updateTotal($el)" @input="updateTotal($el)">
                            @csrf
                            @foreach ($invoices as $invoice)
                                <label class="block text-sm text-gray-700 dark:text-gray-300">{{ $invoice->document_no }} — {{ __('Bayaran Kali Ini (RM)') }}
                                    <input type="number" data-payment-allocation name="allocations[{{ $invoice->id }}]" value="{{ old('allocations.'.$invoice->id) }}" min="0.01" step="0.01" required class="mt-2 w-full rounded-lg border border-gray-300 p-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </label>
                            @endforeach
                            <label class="block text-sm text-gray-700 dark:text-gray-300">{{ __('Amaun Diterima Pada Baucar (RM)') }}<input type="number" name="total_amount" :value="total" readonly min="0.01" step="0.01" required class="mt-2 w-full rounded-lg border border-gray-300 p-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></label>
                            <button class="document-action document-action-primary">{{ __('Simpan Rekod Bayaran') }}</button>
                        </form>
                    </x-common.document-card>
                    @endif
                @endif
            </div>
            <x-common.document-card :title="__('Fail Baucar Bayaran')" class="lg:order-1">
            <p class="mb-4 break-all text-sm text-gray-700 dark:text-gray-300">{{ $document->file_name }}</p>
            @if ($document->file_mime === 'application/pdf')
                <iframe src="{{ route('baucar-bayaran.file', $document->id) }}" title="{{ __('Baucar Bayaran') }}" class="h-[720px] w-full rounded-xl border border-gray-200 dark:border-gray-700"></iframe>
            @else
                <img src="{{ route('baucar-bayaran.file', $document->id) }}" alt="{{ __('Baucar Bayaran') }}" class="max-h-[720px] w-full rounded-xl border border-gray-200 object-contain dark:border-gray-700">
            @endif
            </x-common.document-card>
        </div>
    </x-common.document-workspace>
@endsection
