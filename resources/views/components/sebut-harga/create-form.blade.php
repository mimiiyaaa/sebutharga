@props([
    'customers' => collect(),
    'companies' => collect(),
    'quotation' => null,
    'draft' => null,
    'editing' => false,
    'submitAtTop' => false,
    'updateDraft' => false,
])

@php
    $inputClass = 'w-full min-h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $isEditing = $editing && $quotation && $draft;
    $document = App\Helpers\QuotationDocument::data($draft);
    $recipient = $customers->firstWhere('customer_id', old('customer_id', $quotation->customer_id ?? null));
    $recipientValues = [];
    foreach (['customer_name', 'company_name', 'phone_no', 'email', 'address'] as $field) {
        $recipientValues[$field] = old($field, array_key_exists($field, $document) ? $document[$field] : ($recipient->$field ?? ''));
    }
    $selectedCompany = $companies->firstWhere('company_id', old('company_id', $quotation->company_id ?? null));
    $issuerName = old('issuer_name', array_key_exists('issuer_name', $document) ? $document['issuer_name'] : ($quotation->nama_syarikat ?? ''));
    $issuerPhone = old('issuer_phone', array_key_exists('issuer_phone', $document) ? $document['issuer_phone'] : ($quotation->no_telefon ?? ''));
    $issuerEmail = old('issuer_email', array_key_exists('issuer_email', $document) ? $document['issuer_email'] : ($quotation->emel ?? ''));
    $issuerPersonInCharge = old('issuer_person_in_charge', $document['issuer_person_in_charge'] ?? ($quotation->person_in_charge ?? ''));
    $issuerAddress = old('issuer_address', array_key_exists('issuer_address', $document) ? $document['issuer_address'] : ($quotation->alamat_syarikat ?? ''));
    $confirmationCompany = old('disediakan_company_name', $document['disediakan_company_name'] ?? $issuerName);
    $formAction = $isEditing ? route('sebut-harga.draft.store', $quotation->quotation_id) : route('sebut-harga.store');
    $initialItems = $isEditing
        ? $draft->items->map(fn ($item, $index) => [
            'id' => $index + 1,
            'description' => $item->item_description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'price' => $item->unit_price,
        ])->values()
        : collect([['id' => 1, 'description' => '', 'quantity' => 1, 'unit' => '', 'price' => 0]]);
@endphp

<form method="POST" action="{{ $formAction }}" class="font-outfit space-y-6" x-data="{
    quotationDate: {{ Illuminate\Support\Js::from(old('quotation_date', $isEditing ? $quotation->quotation_date : now()->format('Y-m-d'))) }},
    sequences: {{ Illuminate\Support\Js::from((object) App\Helpers\QuotationNumber::sequences()) }},
    customers: {{ Illuminate\Support\Js::from($customers) }},
    companies: {{ Illuminate\Support\Js::from($companies) }},
    companyId: {{ Illuminate\Support\Js::from((string) old('company_id', $quotation->company_id ?? '')) }},
    issuer: {
        name: {{ Illuminate\Support\Js::from($issuerName) }},
        phone: {{ Illuminate\Support\Js::from($issuerPhone) }},
        email: {{ Illuminate\Support\Js::from($issuerEmail) }},
        personInCharge: {{ Illuminate\Support\Js::from($issuerPersonInCharge) }},
        address: {{ Illuminate\Support\Js::from($issuerAddress) }},
    },
    confirmationCompany: {{ Illuminate\Support\Js::from($confirmationCompany) }},
    get selectedCompany() { return this.companies.find(row => String(row.company_id) === String(this.companyId)); },
    selectCompany() {
        const company = this.selectedCompany;
        if (!company) return;
        this.issuer.name = company.nama_syarikat || '';
        this.issuer.phone = company.no_telefon || '';
        this.issuer.email = company.emel || '';
        this.issuer.personInCharge = company.person_in_charge || '';
        this.issuer.address = company.alamat_syarikat || '';
        this.confirmationCompany = company.nama_syarikat || '';
    },
    customerId: {{ Illuminate\Support\Js::from((string) old('customer_id', $isEditing ? $quotation->customer_id : '')) }},
    get selectedCustomer() { return this.customers.find(row => String(row.customer_id) === String(this.customerId)); },
    recipient: {{ Illuminate\Support\Js::from($recipientValues) }},
    customerName: {{ Illuminate\Support\Js::from($isEditing ? ($quotation->customer_name ?? '') : '') }},
    customerCompany: {{ Illuminate\Support\Js::from($isEditing ? ($quotation->company_name ?? '') : '') }},
    get nextQuotationNo() {
        const year = this.quotationDate.slice(0, 4);
        return year.length === 4 ? 'K1R-QT-' + year + '-' + String((this.sequences[year] || 0) + 1).padStart(3, '0') : '';
    },
    nextId: {{ $initialItems->count() + 1 }},
    items: @js($initialItems),
    subtotal(item) { return Math.round(Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.price) || 0) * 100) / 100 },
    get gross() { return this.items.reduce((sum, item) => sum + this.subtotal(item), 0) },
    money(value) { return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(value) },
    addItem() { this.items.push({ id: this.nextId++, description: '', quantity: 1, unit: '', price: 0 }) }
}">
    @csrf
    @if ($updateDraft && $isEditing)
        <input type="hidden" name="update_draft_id" value="{{ $draft->quotation_detail_id }}">
    @endif
    <input type="hidden" name="status_draft" value="Draf">

    @if ($submitAtTop)
        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600">Kemaskini</button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.document-card title="Maklumat Syarikat">
            <div>
                <label for="company_id" class="{{ $labelClass }}">Nama Syarikat</label>
                <select id="company_id" name="company_id" x-model="companyId" @change="selectCompany()" class="{{ $inputClass }}">
                    <option value="">Pilih syarikat</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->company_id }}">{{ $company->nama_syarikat }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="issuer_name" name="issuer_name" x-model="issuer.name">
            </div>
            <div>
                <label class="{{ $labelClass }}">Nombor Telefon</label>
                <input id="issuer_phone" name="issuer_phone" x-model="issuer.phone" type="tel" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">E-mel</label>
                <input id="issuer_email" name="issuer_email" x-model="issuer.email" type="email" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Person In Charge</label>
                <input id="issuer_person_in_charge" name="issuer_person_in_charge" x-model="issuer.personInCharge" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Alamat Syarikat</label>
                <textarea id="issuer_address" name="issuer_address" rows="3" x-model="issuer.address" class="{{ $inputClass }} h-auto"></textarea>
            </div>
        </x-common.document-card>

        <x-common.document-card title="Maklumat Sebut Harga">
            <div>
                <label for="quotation_no" class="{{ $labelClass }}">No. Sebut Harga</label>
                <input id="quotation_no" readonly value="{{ $isEditing ? $quotation->quotation_no : App\Helpers\QuotationNumber::next(old('quotation_date', now()->format('Y-m-d'))) }}" @if (! $isEditing) :value="nextQuotationNo" @endif class="{{ $inputClass }} bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            </div>
            <div>
                <label for="quotation_date" class="{{ $labelClass }}">Tarikh Sebut Harga</label>
                <input id="quotation_date" name="quotation_date" x-model="quotationDate" type="date" value="{{ $isEditing ? $quotation->quotation_date : now()->format('Y-m-d') }}" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="customer_id" class="{{ $labelClass }}">Pilih Pelanggan</label>
                <select id="customer_id" name="customer_id" x-model="customerId" required class="{{ $inputClass }}" @change="recipient = { customer_name: selectedCustomer?.customer_name || '', company_name: selectedCustomer?.company_name || '', phone_no: selectedCustomer?.phone_no || '', email: selectedCustomer?.email || '', address: selectedCustomer?.address || '' }; customerName = $event.target.selectedOptions[0]?.dataset.name || ''; customerCompany = $event.target.selectedOptions[0]?.dataset.company || ''">
                    <option value="">Pilih pelanggan</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}" data-code="{{ $customer->customer_code }}" data-name="{{ $customer->customer_name }}" data-company="{{ $customer->company_name }}" @selected($isEditing && $quotation->customer_id == $customer->customer_id)>
                            {{ $customer->company_name }}
                            @if ($customer->customer_name)
                                - {{ $customer->customer_name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quotation_title" class="{{ $labelClass }}">Tajuk Sebut Harga</label>
                <input id="quotation_title" name="quotation_title" value="{{ $isEditing ? $quotation->quotation_title : '' }}" maxlength="255" placeholder="Contoh: Pembekalan peralatan pejabat" class="{{ $inputClass }}">
            </div>
        </x-common.document-card>

    </div>

    <x-common.document-card title="Kepada: Pelanggan">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach (['customer_name' => 'Nama Pelanggan', 'company_name' => 'Nama Syarikat', 'phone_no' => 'Nombor Telefon', 'email' => 'E-mel', 'address' => 'Alamat Pelanggan'] as $field => $label)
                <div @class(['sm:col-span-2' => $field === 'address'])>
                    <label for="recipient_{{ $field }}" class="{{ $labelClass }}">{{ __($label) }}</label>
                    @if ($field === 'address')
                        <textarea id="recipient_{{ $field }}" name="{{ $field }}" rows="3" x-model="recipient.{{ $field }}" class="{{ $inputClass }}"></textarea>
                    @else
                        <input id="recipient_{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'phone_no' ? 'tel' : 'text') }}" x-model="recipient.{{ $field }}" class="{{ $inputClass }}">
                    @endif
                </div>
            @endforeach
        </div>
    </x-common.document-card>
    <x-common.document-card title="Item Sebut Harga">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-start text-theme-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-error-800 text-white dark:bg-error-900 dark:text-white">
                    <tr>
                        @foreach (['Bil.', 'Keterangan / Description', 'Kuantiti', 'Unit', 'Harga Seunit (RM)', 'Jumlah (RM)', 'Tindakan'] as $heading)
                            <th scope="col" class="whitespace-nowrap px-3 py-3 text-start text-theme-xs font-semibold">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="p-3 text-center tabular-nums" x-text="index + 1"></td>
                            <td class="min-w-60 p-3"><textarea :name="`items[${index}][description]`" x-model="item.description" rows="3" required class="{{ $inputClass }}" placeholder="Nama item / perkhidmatan"></textarea></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][quantity]`" type="number" min="1" step="1" x-model.number="item.quantity" required class="{{ $inputClass }} text-center"></td>
                            <td class="min-w-32 p-3"><input :name="`items[${index}][unit]`" x-model="item.unit" required class="{{ $inputClass }} text-center" placeholder="Unit"></td>
                            <td class="min-w-40 p-3"><input :name="`items[${index}][price]`" type="number" min="0" step="0.01" x-model.number="item.price" required class="{{ $inputClass }} text-end"></td>
                            <td class="whitespace-nowrap p-3" x-text="money(subtotal(item))"></td>
                            <td class="p-3"><button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" class="text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:text-error-400">Buang</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <button type="button" @click="addItem()" class="rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">+ Tambah Item</button>

        <div class="ms-auto w-full max-w-sm space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" aria-live="polite">
            <div class="flex justify-between gap-4"><span>Jumlah Kasar</span><span x-text="money(gross)"></span></div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-lg font-semibold dark:border-gray-700"><span>Jumlah Total</span><span x-text="money(gross)"></span></div>
        </div>
    </x-common.document-card>

    @php($periods = App\Helpers\QuotationTerms::periods($isEditing ? $draft->terma_syarat : null))
    <x-common.document-card :title="__('Terma dan Syarat')" x-data="{ periods: {{ Illuminate\Support\Js::from(collect($periods)->mapWithKeys(fn ($value, $key) => [$key => old($key, $value)])) }}, insertNextTerm(event) { const field = event.target; const before = field.value.slice(0, field.selectionStart); const after = field.value.slice(field.selectionEnd); const nextNumber = 4 + before.split(/\r?\n/).length; const insertion = `\n${nextNumber}. `; field.value = before + insertion + after; field.dispatchEvent(new Event('input', { bubbles: true })); requestAnimationFrame(() => { const cursor = before.length + insertion.length; field.setSelectionRange(cursor, cursor); }); } }">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            @foreach (['validity_days' => 'Tempoh Sah (Hari)', 'delivery_min_days' => 'Penghantaran Minimum (Hari)', 'delivery_max_days' => 'Penghantaran Maksimum (Hari)'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ __($label) }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" x-model="periods.{{ $field }}" type="number" min="1" max="3650" required value="{{ old($field, $periods[$field]) }}" class="{{ $inputClass }}">
                </div>
            @endforeach
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Tempoh sah dikira dari Tarikh Sebut Harga. Tempoh penghantaran dikira selepas penerimaan PO rasmi.') }}</p>
        <div class="space-y-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
            <p>1. Tempoh sah sebutharga adalah selama <span class="font-semibold" x-text="periods.validity_days || '—'"></span> hari dari tarikh sebutharga dikeluarkan.</p>
            <p>2. Tempoh penghantaran: <span class="font-semibold" x-text="periods.delivery_min_days || '—'"></span> - <span class="font-semibold" x-text="periods.delivery_max_days || '—'"></span> hari selepas penerimaan Pesanan Belian (PO) rasmi.</p>
            <p>3. {{ __('Sila tandatangan di bawah untuk pengesahan persetujuan sebutharga ini.') }}</p>
        </div>
        <div>
            <label for="additional_terms" class="{{ $labelClass }}">{{ __('Terma Tambahan') }}</label>
            <textarea id="additional_terms" name="additional_terms" rows="4" maxlength="10000" @keydown.enter.prevent="insertNextTerm($event)" class="{{ $inputClass }}">{{ old('additional_terms', array_key_exists('additional_terms', $document) && trim($document['additional_terms']) !== '' ? $document['additional_terms'] : '4. ') }}</textarea>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Tekan Enter untuk sambung nombor terma secara automatik bermula daripada 4.') }}</p>
        </div>
    </x-common.document-card>
    <x-common.document-card title="Pengesahan Sebut Harga">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                <div class="space-y-5">
                    <div>
                        <label for="disediakan_oleh_confirmation" class="{{ $labelClass }}">{{ __('Disediakan Oleh (Jawatan)') }}</label>
                        <input id="disediakan_oleh_confirmation" name="disediakan_oleh" value="{{ old('disediakan_oleh', $isEditing ? ($draft->disediakan_oleh ?? '') : '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="disediakan_company_name" class="{{ $labelClass }}">{{ __('Nama Syarikat') }}</label>
                        <input id="disediakan_company_name" name="disediakan_company_name" x-model="confirmationCompany" class="{{ $inputClass }}">
                    </div>
                </div>
                <div class="h-16" aria-hidden="true"></div>
                <div class="min-h-16 border-t border-dashed border-gray-400 pt-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">{{ __('Ruang Tandatangan') }}</div>
            </div>
            <div class="flex min-h-80 flex-col rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30 sm:p-6">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Diterima / Disahkan Oleh') }}</p>
                <div class="min-h-40 flex-1" aria-hidden="true"></div>
                <div class="mx-auto min-h-16 w-full max-w-xs border-t border-dotted border-gray-400 pt-3 text-center text-sm leading-6 dark:border-gray-600">
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ __('Tandatangan & Cop') }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Tarikh') }}</p>
                </div>
            </div>
        </div>
    </x-common.document-card>

        <x-common.document-card title="Maklumat Tambahan">
            <div>
                <label for="maklumat_tambahan" class="{{ $labelClass }}">Maklumat Tambahan</label>
                <textarea id="maklumat_tambahan" name="maklumat_tambahan" rows="5" class="{{ $inputClass }} h-auto" placeholder="Masukkan maklumat tambahan jika ada">{{ old('maklumat_tambahan', $document['maklumat_tambahan'] ?? ($draft->maklumat_tambahan ?? '')) }}</textarea>
            </div>
            <div>
                <label for="catatan" class="{{ $labelClass }}">Catatan</label>
                <textarea id="catatan" name="catatan" rows="3" class="{{ $inputClass }} h-auto" placeholder="Masukkan catatan atau arahan khas">{{ old('catatan', $document['catatan'] ?? ($draft->catatan ?? '')) }}</textarea>
            </div>
        </x-common.document-card>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a @if ($isEditing) href="#" @click.prevent="editing = false" @else href="{{ route('sebut-harga') }}" @endif class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{{ __($isEditing ? 'Batal Kemaskini' : 'Kembali') }}</a>
        @if (! $submitAtTop)
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-600">{{ $isEditing ? 'Kemaskini' : 'Simpan Sebut Harga' }}</button>
        @endif
    </div>
</form>
