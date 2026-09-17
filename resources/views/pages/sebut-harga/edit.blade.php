@extends('layouts.app')

@section('content')

    <x-common.document-workspace :title="__('Maklumat Sebut Harga')" :subtitle="$quotation->quotation_no" :reference="__('Draf') . ' ' . $draft->draft_no">
    <a href="{{ route('sebut-harga') }}" class="mb-5 inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali ke Senarai') }}</a>
    <div x-data="{ editing: false, editMode: 'existing' }">
        <div x-show="!editing" x-cloak class="mb-6 shadow-theme-xs rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div class="flex w-full flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Maklumat Sebut Harga</h3>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('sebut-harga.preview', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id]) }}" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Pratonton Dokumen') }}</a>
                        @if ($draft->status_draft !== 'Final')
                            <button type="button" @click="editMode = 'existing'; editing = true" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">Edit</button>
                            <button type="button" @click="editMode = 'new'; editing = true" class="rounded-lg border border-brand-300 px-4 py-3 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">Draf Baharu</button>
                            <form method="POST" action="{{ route('sebut-harga.draft.approve', [$quotation->quotation_id, $draft->quotation_detail_id]) }}" onsubmit="return confirm('Muktamadkan draf ini? Draf lain akan kekal sebagai draf.')">
                                @csrf
                                <button type="submit" class="rounded-lg bg-success-600 px-4 py-3 text-sm font-medium text-white hover:bg-success-700">{{ __('Muktamadkan Draf') }}</button>
                            </form>
                        @else
                            <span class="rounded-lg bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Draf Dimuktamadkan') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 xl:grid-cols-2">
                <div><p class="text-sm text-gray-500 dark:text-gray-400">No. Sebut Harga</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_no }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Tarikh</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_date }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Pelanggan / Syarikat</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->company_name ?: $quotation->customer_name }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Draf</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">Draf {{ $draft->draft_no }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Kemaskini Terakhir</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $draft->updated_at ? \Carbon\Carbon::parse($draft->updated_at)->format('d/m/Y H:i') : '-' }}</p></div>
            </div>
            <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Tajuk</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_title ?: '-' }}</p></div>
        </div>

        <div x-show="!editing" x-cloak class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Item Sebut Harga</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maklumat draf ini dalam paparan terkunci</p>
                </div>
                <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $draft->status_draft ?: 'Draf' }}</span>
            </div>
            <div class="custom-scrollbar overflow-x-auto">
                <table class="w-full min-w-[760px] text-start text-theme-sm">
                    <thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white">
                        <tr>
                            <th scope="col" class="w-14 px-5 py-4 text-center text-theme-xs font-semibold">{{ __('Bil') }}</th>
                            <th scope="col" class="w-2/5 px-5 py-4 text-start text-theme-xs font-semibold">Perihal Barangan</th>
                            <th scope="col" class="px-5 py-4 text-center text-theme-xs font-semibold">Kuantiti</th>
                            <th scope="col" class="px-5 py-4 text-center text-theme-xs font-semibold">Unit</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 text-end text-theme-xs font-semibold">Harga Seunit (RM)</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4 text-end text-theme-xs font-semibold">Jumlah (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($draft->items as $item)
                            <tr class="border-b border-gray-100 even:bg-gray-50/60 hover:bg-brand-50/40 dark:border-gray-800 dark:even:bg-gray-800/30 dark:hover:bg-brand-500/5">
                                <td class="px-5 py-5 text-center tabular-nums text-gray-400 dark:text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-5 py-5 font-medium leading-6 text-gray-800 dark:text-gray-200"><p class="whitespace-pre-line break-words">{{ $item->item_description }}</p></td>
                                <td class="whitespace-nowrap px-5 py-5 text-center tabular-nums text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-center tabular-nums text-gray-600 dark:text-gray-300">{{ $item->unit }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-end tabular-nums text-gray-600 dark:text-gray-300">RM {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-5 text-end font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Tiada item</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-gray-100 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex w-full flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 sm:w-auto sm:min-w-80">
                    <span class="text-theme-sm font-medium">Jumlah Total</span>
                    <span class="text-xl font-semibold tabular-nums">RM {{ number_format((float) $draft->jumlah_total, 2) }}</span>
                </div>
            </div>
        </div>

        <div x-show="editing && editMode === 'existing'" x-cloak class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Kemaskini Sebut Harga') }}</h2>
                <button type="button" @click="editing = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</button>
            </div>
            <x-sebut-harga.create-form
                :customers="$customers"
                :quotation="$quotation"
                :draft="$draft"
                :editing="true"
                :submit-at-top="false"
                :update-draft="true"
            />
        </div>
        <div x-show="editing && editMode === 'new'" x-cloak class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Draf Baharu</h2>
                <button type="button" @click="editing = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Kembali</button>
            </div>
            <x-sebut-harga.create-form
                :customers="$customers"
                :quotation="$quotation"
                :draft="$draft"
                :editing="true"
                :submit-at-top="false"
            />
        </div>
    </div>
    </x-common.document-workspace>
@endsection
