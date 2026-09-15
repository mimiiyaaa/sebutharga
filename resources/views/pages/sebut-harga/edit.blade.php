@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Sebut Harga" />

    <div x-data="{ editing: false }">
        <div x-show="!editing" x-cloak class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div class="flex w-full items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Maklumat Sebut Harga</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editing = true" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">Kemaskini</button>
                        <form method="POST" action="{{ route('sebut-harga.draft.approve', [$quotation->quotation_id, $draft->quotation_detail_id]) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-success-600 px-4 py-3 text-sm font-medium text-white hover:bg-success-700">Setuju</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 xl:grid-cols-4">
                <div><p class="text-sm text-gray-500 dark:text-gray-400">No. Sebut Harga</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_no }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Tarikh</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_date }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Pelanggan / Syarikat</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $quotation->company_name ?: $quotation->customer_name }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Versi</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90">Versi {{ $draft->draft_no }}</p></div>
            </div>
            <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Tajuk</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_title ?: '-' }}</p></div>
        </div>

        <div x-show="!editing" x-cloak class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Item Sebut Harga</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maklumat versi ini dalam paparan terkunci</p>
                </div>
                <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $draft->status_draft ?: 'Draf' }}</span>
            </div>
            <div class="overflow-x-auto px-6 py-2">
                <table class="w-full text-start text-sm">
                    <thead class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-start font-medium">Keterangan</th>
                            <th class="px-3 py-3 text-start font-medium">Kuantiti</th>
                            <th class="px-3 py-3 text-start font-medium">Unit</th>
                            <th class="px-3 py-3 text-start font-medium">Harga Seunit</th>
                            <th class="px-3 py-3 text-end font-medium">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($draft->items as $item)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-3 text-gray-800 dark:text-gray-300">{{ $item->item_description }}</td>
                                <td class="px-3 py-3 text-gray-800 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-gray-800 dark:text-gray-300">{{ $item->unit }}</td>
                                <td class="px-3 py-3 text-gray-800 dark:text-gray-300">RM {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-3 py-3 text-end font-medium text-gray-800 dark:text-gray-300">RM {{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Tiada item</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-gray-100 px-6 py-5 dark:border-gray-800">
                <div class="flex min-w-64 items-center justify-between gap-8 text-lg font-semibold text-gray-800 dark:text-white/90">
                    <span>Jumlah Total</span>
                    <span>RM {{ number_format((float) $draft->jumlah_total, 2) }}</span>
                </div>
            </div>
        </div>

        <div x-show="editing" x-cloak>
            <x-sebut-harga.create-form
                :customers="$customers"
                :quotation="$quotation"
                :draft="$draft"
                :editing="true"
                :submit-at-top="false"
            />
        </div>
    </div>
@endsection
