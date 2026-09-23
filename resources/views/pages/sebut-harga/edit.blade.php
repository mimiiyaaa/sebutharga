@extends('layouts.app')

@section('content')

    @php
        $isFinalView = request('from') === 'final';
    @endphp
    <x-common.document-workspace :title="__('Butiran Sebut Harga')" :subtitle="$quotation->quotation_no" :reference="($isFinalView ? __('Versi') . ' ' . ($draft->final_no ?? $draft->draft_no) : __('Draf') . ' ' . $draft->draft_no)" x-data="{ editing: {{ request('edit') ? 'true' : 'false' }}, editMode: 'existing', loginInfoOpen: false, sendModalOpen: false }" @keydown.escape.window="loginInfoOpen = false; sendModalOpen = false">
    <x-slot:referenceActions><div class="relative" @click.outside="loginInfoOpen = false"><button type="button" @click="loginInfoOpen = !loginInfoOpen" title="{{ __('Maklumat Login') }}" aria-label="{{ __('Maklumat Login') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:text-brand-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16v-4m0-4h.01M21 12a9 9 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></button><x-common.created-by-panel :name="$createdBy?->name" :jawatan="$createdBy?->jawatan" :email="$createdBy?->email" :created-at="$draft->created_at" document-label="draf ini" /></div></x-slot>
    <div>
        @php
            $document = App\Helpers\QuotationDocument::data($draft);
            $value = fn (string $field, $fallback = null) => filled($document[$field] ?? null) ? $document[$field] : ($fallback ?? '—');
            $issuer = [
                'Nama Syarikat' => $value('issuer_name', $quotation->nama_syarikat),
                'Nombor Telefon' => $value('issuer_phone', $quotation->no_telefon),
                'E-mel' => $value('issuer_email', $quotation->emel),
                'Person In Charge' => $value('issuer_person_in_charge', $quotation->person_in_charge),
                'Alamat Syarikat' => $value('issuer_address', $quotation->alamat_syarikat),
            ];
            $recipient = [
                'Nama Pelanggan' => $value('customer_name', $quotation->customer_name),
                'Nama Syarikat' => $value('company_name', $quotation->company_name),
                'Nombor Telefon' => $value('phone_no', $quotation->phone_no),
                'E-mel' => $value('email', $quotation->email),
                'Alamat' => $value('address', $quotation->address),
            ];
        @endphp
        <div x-show="!editing" x-cloak class="mb-6 shadow-theme-xs rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div class="flex w-full items-center justify-between gap-4">
                    <h3 class="shrink-0 text-lg font-semibold text-gray-800 dark:text-white/90">Maklumat Sebut Harga</h3>
                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                        <a href="{{ route('sebut-harga.preview', [$quotation->quotation_id, 'draft' => $draft->quotation_detail_id, 'from' => request('from') === 'final' ? 'final' : 'draft', 'via' => 'detail']) }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5" stroke-width="1.8"/></svg>{{ __('Pratonton') }}</a>
                        @if (! $isFinalView)
                            <button type="button" @click="editMode = 'existing'; editing = true" class="inline-flex h-10 items-center gap-2 rounded-lg bg-brand-500 px-3 text-sm font-medium text-white transition hover:bg-brand-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m14 6 4 4M4 20l4.5-1 9.8-9.8a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z"/></svg>Edit</button>
                            <button type="button" @click="editMode = 'new'; editing = true" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M8 13h6m-6 4h6"/></svg>{{ __('Draf Baharu') }}</button>
                            @if (strtolower(trim((string) $draft->status_draft)) === 'final')
                                <span class="inline-flex h-10 items-center rounded-lg bg-success-50 px-3 text-sm font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Sudah Difinalisekan') }}</span>
                            @else
                                <form class="inline-flex shrink-0" method="POST" action="{{ route('sebut-harga.draft.approve', [$quotation->quotation_id, $draft->quotation_detail_id]) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Muktamadkan draf ini? Draf lain akan kekal sebagai draf.', button: 'Muktamadkan' })">
                                    @csrf
                                    <button type="submit" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-brand-500 px-3 text-sm font-medium text-white transition hover:bg-brand-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>{{ __('Muktamadkan') }}</button>
                                </form>
                            @endif
                        @else
                            <form method="POST" action="{{ route('sebut-harga.final.new', [$quotation->quotation_id, $draft->quotation_detail_id]) }}">
                                @csrf
                                <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-brand-300 px-4 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('Cipta Draf Baharu') }}</button>
                            </form>
                            @if (empty($draft->sent_at))
                                <button type="button" @click="sendModalOpen = true" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Hantar') }}</button>
                            @elseif ($draft->status_quotation === null)
                                <form method="POST" action="{{ route('sebut-harga.final.agree', [$quotation->quotation_id, $draft->quotation_detail_id]) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Tandakan sebut harga ini sebagai setuju?', button: 'Setuju' })">
                                    @csrf
                                    <button type="submit" class="inline-flex h-11 items-center rounded-lg bg-success-500 px-4 text-sm font-medium text-white transition hover:bg-success-600">{{ __('Setuju') }}</button>
                                </form>
                                <form method="POST" action="{{ route('sebut-harga.final.undo-send', [$quotation->quotation_id, $draft->quotation_detail_id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Undo Hantar') }}</button>
                                </form>
                            @else
                                <span class="inline-flex h-11 items-center rounded-lg {{ (int) $draft->status_quotation === 1 ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' }} px-4 text-sm font-medium">{{ (int) $draft->status_quotation === 1 ? __('Setuju') : __('Tidak Setuju') }}</span>
                                <form method="POST" action="{{ route('sebut-harga.final.undo-decision', [$quotation->quotation_id, $draft->quotation_detail_id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Undo Keputusan') }}</button>
                                </form>
                            @endif
                            <span class="inline-flex h-11 items-center rounded-lg bg-success-50 px-4 text-sm font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Final') }} {{ $draft->final_no ?? $draft->draft_no }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 xl:grid-cols-3">
                <div><p class="text-sm text-gray-500 dark:text-gray-400">No. Sebut Harga</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_no }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Tarikh</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_date }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Pelanggan / Syarikat</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->company_name ?: $quotation->customer_name }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ $isFinalView ? __('Versi') : __('Draf') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $isFinalView ? __('Versi') . ' ' . ($draft->final_no ?? $draft->draft_no) : __('Draf') . ' ' . $draft->draft_no }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">Kemaskini Terakhir</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $draft->updated_at ? \Carbon\Carbon::parse($draft->updated_at)->format('d/m/Y H:i') : '-' }}</p></div>
                <div><p class="text-sm text-gray-500 dark:text-gray-400">No. Rujukan Pelanggan</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->no_rujukan_pelanggan ?: '—' }}</p></div>
                @if ($draft->sent_at)
                    <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Dihantar Oleh') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $draft->sent_by ?: '—' }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tarikh & Masa Hantar') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ \Carbon\Carbon::parse($draft->sent_at)->format('d/m/Y H:i') }}</p></div>
                @endif
            </div>
            <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Tajuk</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $quotation->quotation_title ?: '-' }}</p></div>
            <div class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6 dark:border-gray-800 lg:grid-cols-2">
                @foreach (['Maklumat Syarikat Pengeluar' => $issuer, 'Maklumat Pelanggan' => $recipient] as $section => $fields)
                    <section class="border-t border-gray-100 pt-5 first:border-t-0 first:pt-0 dark:border-gray-800">
                        <h4 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">{{ __($section) }}</h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            @foreach ($fields as $label => $fieldValue)
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __($label) }}</p>
                                    <p class="mt-2 whitespace-pre-line break-words text-base font-medium text-gray-800 dark:text-white/90">{{ $fieldValue }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
            <div class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6 dark:border-gray-800 lg:grid-cols-2">
                <section class="border-t border-gray-100 pt-5 dark:border-gray-800 lg:border-t-0 lg:border-s lg:ps-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Terma dan Syarat') }}</h4>
                    <p class="whitespace-pre-line text-base leading-6 text-gray-700 dark:text-gray-300">{{ $draft->terma_syarat ?: '—' }}</p>
                </section>
                <section class="border-t border-gray-100 pt-5 dark:border-gray-800 lg:border-t-0 lg:border-s lg:ps-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Pengesahan Sebut Harga') }}</h4>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Disediakan Oleh') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $draft->disediakan_oleh ?: '—' }}</p></div>
                        <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nama Syarikat') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $value('disediakan_company_name', $quotation->nama_syarikat) }}</p></div>
                        <div><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Diterima Oleh') }}</p><p class="mt-2 text-base font-medium text-gray-800 dark:text-white/90">{{ $draft->diterima_oleh ?: '—' }}</p></div>
                    </div>
                </section>
            </div>
        </div>

        <div x-show="!editing" x-cloak class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Tambahan') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Maklumat dalaman ini dipaparkan dalam sistem sahaja dan tidak dimasukkan ke dalam PDF.') }}</p>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                <section>
                    <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Tambahan') }}</h4>
                    <p class="whitespace-pre-line break-words text-base leading-6 text-gray-700 dark:text-gray-300">{{ filled($document['maklumat_tambahan'] ?? null) ? $document['maklumat_tambahan'] : '—' }}</p>
                </section>
                <section class="border-t border-gray-100 pt-6 dark:border-gray-800 lg:border-t-0 lg:border-s lg:ps-6 lg:pt-0">
                    <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Catatan') }}</h4>
                    <p class="whitespace-pre-line break-words text-base leading-6 text-gray-700 dark:text-gray-300">{{ filled($document['catatan'] ?? null) ? $document['catatan'] : '—' }}</p>
                </section>
            </div>
        </div>

        <div x-show="!editing" x-cloak class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Item Sebut Harga</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maklumat draf ini dalam paparan terkunci</p>
                </div>
                <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $isFinalView ? ($draft->status_draft ?: 'Final') : 'Draf' }}</span>
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

        <div x-show="!editing" x-cloak class="flex justify-end">
            <a href="{{ request('from') === 'final' ? route('sebut-harga.final', ['draft' => $draft->quotation_detail_id]) : route('sebut-harga') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Kembali') }}</a>
        </div>

        <div x-show="editing && editMode === 'existing'" x-cloak class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Kemaskini Sebut Harga') }}</h2>
            </div>
            <x-sebut-harga.create-form
                :customers="$customers"
                :companies="$companies"
                :supplier-quotations="$supplierQuotations"
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
            </div>
            <x-sebut-harga.create-form
                :customers="$customers"
                :companies="$companies"
                :supplier-quotations="$supplierQuotations"
                :quotation="$quotation"
                :draft="$draft"
                :editing="true"
                :submit-at-top="false"
            />
        </div>
    </div>
    @if ($isFinalView && strtolower(trim((string) $draft->status_draft)) === 'final' && empty($draft->sent_at))
        <div x-cloak x-show="sendModalOpen" x-transition.opacity class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 p-4" @click.self="sendModalOpen = false">
            <form method="POST" action="{{ route('sebut-harga.final.send', [$quotation->quotation_id, $draft->quotation_detail_id]) }}" x-show="sendModalOpen" x-transition class="w-full max-w-md rounded-2xl bg-white shadow-theme-xl dark:bg-gray-900">
                @csrf
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-800"><div><h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Maklumat Penghantaran') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapkan maklumat sebelum menghantar sebut harga.') }}</p></div><button type="button" @click="sendModalOpen = false" class="text-xl text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90" aria-label="{{ __('Tutup') }}">&times;</button></div>
                <div class="space-y-4 px-6 py-5"><div><label for="sent_by" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Dihantar Oleh') }}</label><input id="sent_by" name="sent_by" required value="{{ old('sent_by', auth()->user()?->name) }}" class="w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="sent_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Tarikh Hantar') }}</label><input id="sent_date" name="sent_date" type="text" inputmode="numeric" required value="{{ old('sent_date', now()->format('m/d/Y')) }}" placeholder="mm/dd/yyyy" class="w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div><div><label for="sent_time" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Masa Hantar') }}</label><input id="sent_time" name="sent_time" type="text" required value="{{ old('sent_time', now()->format('h:i A')) }}" placeholder="hh:mm AM/PM" class="w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div></div></div>
                <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800"><button type="button" @click="sendModalOpen = false" class="inline-flex h-10 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Batal') }}</button><button type="submit" class="inline-flex h-10 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">{{ __('Hantar') }}</button></div>
            </form>
        </div>
    @endif
    </x-common.document-workspace>
@endsection
