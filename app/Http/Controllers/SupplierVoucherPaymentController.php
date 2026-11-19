<?php

namespace App\Http\Controllers;

use App\Services\InvoicePayments;
use App\Services\SupplierVoucherDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupplierVoucherPaymentController extends Controller
{
    public function create(Request $request)
    {
        if ($request->query('mode') === 'payment' && $request->filled('invoice_id')) {
            $pendingVoucher = DB::table('supplier_invoice_payments as p')
                ->join('payment_voucher_documents as v', 'v.id', '=', 'p.payment_voucher_id')
                ->where('p.supplier_document_id', $request->integer('invoice_id'))
                ->where('v.voucher_type', 'supplier')->where('v.supplier_payment_status', 'unpaid')
                ->orderBy('v.id')->value('v.id');
            if ($pendingVoucher) return redirect()->route('baucar-bayaran.supplier.edit', [$pendingVoucher, 'mode' => 'payment']);
        }
        if ($request->filled('invoice_id')) return $this->form($request);
        return view('pages.baucar-bayaran.supplier-select-source', ['title' => __('Tambah Baucar Pembekal')]);
    }

    public function form(Request $request, ?int $id = null)
    {
        $document = $id ? DB::table('payment_voucher_documents')->where('id', $id)->where('voucher_type', 'supplier')->firstOrFail() : null;
        $standalone = $document ? (json_decode($document->supplier_details ?? '{}', true)['source_type'] ?? 'invoice') === 'standalone' : $request->query('source') === 'standalone';
        $suppliers = DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')->get();
        $invoices = DB::table('supplier_documents')->where('kind', 'invoice')->orderByDesc('document_date')->get();
        $otherPayments = DB::table('supplier_invoice_payments as p')->join('payment_voucher_documents as v', 'v.id', '=', 'p.payment_voucher_id')
            ->get(['p.*', 'v.supplier_payment_status', 'v.voucher_date'])->groupBy('supplier_document_id');
        foreach ($invoices as $invoice) {
            $all = $otherPayments->get($invoice->id, collect());
            $other = $all->filter(fn ($payment) => !$id || (int) $payment->payment_voucher_id !== $id);
            $invoice->available = max(0, InvoicePayments::cents($invoice->total_amount) - $other->sum(fn ($p) => InvoicePayments::cents($p->amount))) / 100;
            $invoice->paid = $all->filter(fn ($p) => $p->supplier_payment_status === 'paid' && $p->voucher_date <= now('Asia/Kuala_Lumpur')->toDateString())->sum(fn ($p) => InvoicePayments::cents($p->amount)) / 100;
            $invoice->balance = max(0, InvoicePayments::cents($invoice->total_amount) - InvoicePayments::cents($invoice->paid)) / 100;
        }
        $selectedInvoice = $request->filled('invoice_id') ? $invoices->firstWhere('id', $request->integer('invoice_id')) : null;
        if ($request->filled('invoice_id')) abort_unless($selectedInvoice, 404);
        $selectedSupplierId = $document?->supplier_id ?? $selectedInvoice?->supplier_id ?? old('supplier_id', '');
        $supplier = $suppliers->firstWhere('customer_id', $selectedSupplierId);
        $details = SupplierVoucherDetails::values($document, $supplier);
        foreach ($details as $field => $value) $details[$field] = old($field, $value);
        $links = $id ? DB::table('supplier_invoice_payments')->where('payment_voucher_id', $id)->orderBy('supplier_document_id')->get() : collect();
        $payments = old('payments', $links->map(fn ($p) => ['invoice_id' => (string) $p->supplier_document_id, 'amount' => (float) $p->amount])->all());
        if ($links->isNotEmpty()) {
            $submitted = collect(old('payments', []))->keyBy('invoice_id');
            $payments = $links->map(fn ($p) => ['invoice_id' => (string) $p->supplier_document_id, 'amount' => $submitted->get($p->supplier_document_id)['amount'] ?? (float) $p->amount])->all();
        }
        $sourceInvoice = $links->isNotEmpty() ? $invoices->firstWhere('id', $links->first()->supplier_document_id) : $selectedInvoice;
        $details['supplier_name'] = $standalone ? old('supplier_name', $document?->payee_name ?? $supplier?->company_name ?? '') : ($sourceInvoice?->supplier_name ?? $document?->payee_name ?? $supplier?->company_name ?? '');
        if (!$payments) $payments = [['invoice_id' => (string) ($selectedInvoice?->id ?? ''), 'amount' => $document?->total_amount ?? '']];
        if ($selectedInvoice && !$document && !$standalone) {
            $payments = [['invoice_id' => (string) $selectedInvoice->id, 'amount' => old('payments.0.amount', '')]];
        }
        $year = now()->year;
        $manualItems = $id && $standalone ? DB::table('payment_voucher_supplier_item')->where('payment_voucher_id', $id)->orderBy('id')->get()->map(fn ($item) => ['description' => $item->item_description, 'amount' => $item->subtotal])->all() : [];
        if (!$manualItems) $manualItems = [['description' => $document?->description ?? '', 'amount' => $document?->total_amount ?? '']];
        $numbers = DB::table('payment_voucher_documents')->where('voucher_no', 'like', "K1R-PV-$year-%")->pluck('voucher_no');
        $next = $numbers->map(fn ($n) => (int) substr($n, strlen("K1R-PV-$year-")))->max() + 1;
        // Keep existing references visible when editing, but offer only outstanding invoices for new selections.
        $retainedInvoiceIds = $links->pluck('supplier_document_id')->push($selectedInvoice?->id)->filter();
        $invoices = $invoices->filter(fn ($invoice) => ($invoice->balance > 0 && $invoice->available > 0) || $retainedInvoiceIds->contains($invoice->id));
        return view('pages.baucar-bayaran.supplier', [
            'recordPayment' => $request->query('mode') === 'payment',
            'standalone' => $standalone,
            'initialManualItems' => array_values(old('manual_items', $manualItems)),
            'title' => __($request->query('mode') === 'payment' ? 'Rekod Bayaran Pembekal' : ($document ? 'Edit Baucar Kepada Pembekal' : 'Tambah Baucar Pembekal')),
            'document' => $document, 'suppliers' => $suppliers, 'invoiceOptions' => $invoices->values(),
            'companies' => DB::table('companies')->orderBy('nama_syarikat')->get(), 'details' => $details,
            'referencesLocked' => !$standalone && ($links->isNotEmpty() || $selectedInvoice !== null), 'selectedSupplierId' => $selectedSupplierId, 'initialPayments' => array_values($payments),
            'nextVoucherNo' => $document?->voucher_no ?? "K1R-PV-$year-".str_pad($next, 3, '0', STR_PAD_LEFT),
            'preparedBy' => old('prepared_by', $document?->prepared_by ?? $request->user()->name),
            'preparedRole' => old('prepared_role', $document?->prepared_role ?? config('purchase_order.prepared_role')),
            'receivedBy' => old('received_by', $document?->received_by ?? $supplier?->company_name),
            'receivedRole' => old('received_role', $document?->received_role ?? 'Penerima'),
            'chequeNo' => old('cheque_no', $document?->cheque_no),
        ]);
    }

    public function save(Request $request, ?int $id = null)
    {
        $existing = $id ? DB::table('payment_voucher_documents')->where('id', $id)->where('voucher_type', 'supplier')->firstOrFail() : null;
        $standalone = $existing ? (json_decode($existing->supplier_details ?? '{}', true)['source_type'] ?? 'invoice') === 'standalone' : $request->input('source_type', 'invoice') === 'standalone';
        $data = $request->validate([
            'source_type' => ['nullable', Rule::in(['invoice', 'standalone'])],
            'record_payment' => 'nullable|boolean',
            'total_amount' => 'nullable',
            'manual_items' => $standalone ? 'required|array|min:1|max:100' : 'prohibited',
            'manual_items.*.description' => 'required|string|max:5000',
            'manual_items.*.amount' => 'required|numeric|min:0.01|max:9999999999.99|decimal:0,2',
            'supplier_id' => [$standalone ? 'nullable' : 'required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 2)],
            'voucher_no' => ['required', 'string', 'max:100', Rule::unique('payment_voucher_documents', 'voucher_no')->ignore($id)],
            'voucher_date' => 'required|date_format:Y-m-d',
            'payment_method' => ['required', Rule::in(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'])],
            'cheque_no' => 'nullable|string|max:100', 'description' => 'nullable|string|max:5000',
            'payments' => $standalone ? 'prohibited' : 'required|array|min:1|max:100', 'payments.*.invoice_id' => 'required|integer|distinct',
            'payments.*.amount' => 'required|numeric|min:0.01|max:9999999999.99|decimal:0,2',
            'issuer_name' => 'required|string|max:255', 'issuer_address' => 'nullable|string|max:2000',
            'issuer_phone' => 'nullable|string|max:50', 'issuer_email' => 'nullable|email|max:255',
            'supplier_name' => 'required|string|max:255', 'supplier_person' => 'nullable|string|max:255',
            'supplier_address' => 'nullable|string|max:2000', 'supplier_phone' => 'nullable|string|max:50', 'supplier_email' => 'nullable|email|max:255',
            'prepared_by' => 'required|string|max:255', 'prepared_role' => 'required|string|max:255',
            'received_by' => 'nullable|string|max:255', 'received_role' => 'nullable|string|max:255',
        ]);
        $data['payments'] = $standalone ? [] : $data['payments'];
        // Payment status is managed only after saving, through the voucher detail page.
        $data['supplier_payment_status'] = $request->boolean('record_payment') ? 'paid' : ($existing?->supplier_payment_status ?? 'unpaid');
        if ($standalone) $data['description'] = null;
        if ($standalone) $data['description'] = null;
        $data['supplier_id'] = $data['supplier_id'] ?? null;
        $newPath = 'payment-vouchers/supplier/'.Str::uuid().'.pdf';
        $oldPath = null;
        try {
            $voucherId = DB::transaction(function () use ($data, $request, $id, $existing, $newPath, $standalone, &$oldPath) {
                $locked = $id ? DB::table('payment_voucher_documents')->where('id', $id)->where('voucher_type', 'supplier')->lockForUpdate()->firstOrFail() : null;
                $oldPath = $locked?->file_path;
                $data['supplier_payment_status'] = $request->boolean('record_payment') ? 'paid' : ($locked?->supplier_payment_status ?? 'unpaid');
                $oldIds = $id ? DB::table('supplier_invoice_payments')->where('payment_voucher_id', $id)->pluck('supplier_document_id') : collect();
                if ($locked && !$standalone && (int) $locked->supplier_id !== (int) $data['supplier_id']) {
                    throw ValidationException::withMessages(['supplier_id' => __('Pembekal asal baucar tidak boleh ditukar.')]);
                }
                $submittedIds = collect($data['payments'])->pluck('invoice_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                if ($oldIds->isNotEmpty() && $oldIds->map(fn ($id) => (int) $id)->sort()->values()->all() !== $submittedIds) {
                    throw ValidationException::withMessages(['payments' => __('Invois rujukan asal tidak boleh ditukar, ditambah atau dibuang semasa edit baucar.')]);
                }
                $ids = collect($data['payments'])->pluck('invoice_id')->merge($oldIds)->unique()->sort()->values();
                $invoices = DB::table('supplier_documents')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $payments = collect($data['payments'])->sortBy('invoice_id');
                $items = collect(); $total = 0; $rows = [];
                foreach ($payments as $index => $payment) {
                    $invoice = $invoices->get($payment['invoice_id']);
                    if (!$invoice || $invoice->kind !== 'invoice' || (int) $invoice->supplier_id !== (int) $data['supplier_id'] || $invoice->total_amount === null) {
                        throw ValidationException::withMessages(['payments.'.$index.'.invoice_id' => __('Pilih invois daripada pembekal yang sama dan pastikan jumlah invois telah diisi.')]);
                    }
                    $used = DB::table('supplier_invoice_payments')->where('supplier_document_id', $invoice->id)->when($id, fn ($q) => $q->where('payment_voucher_id', '!=', $id))->get()->sum(fn ($p) => InvoicePayments::cents($p->amount));
                    $amount = InvoicePayments::cents($payment['amount']);
                    if ($amount > InvoicePayments::cents($invoice->total_amount) - $used) {
                        throw ValidationException::withMessages(['payments.'.$index.'.amount' => __('Amaun melebihi baki yang tersedia. Semak baucar lain untuk invois ini.')]);
                    }
                    $total += $amount;
                    $rows[] = ['supplier_document_id' => $invoice->id, 'amount' => $amount / 100, 'recorded_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
                    $items->push((object) ['item_description' => __('Bayaran invois').' '.$invoice->document_no, 'quantity' => 1, 'unit' => 'bayaran', 'unit_price' => $amount / 100, 'subtotal' => $amount / 100, 'invoice_no' => $invoice->document_no]);
                }
                if ($total > 999999999999) throw ValidationException::withMessages(['payments' => __('Jumlah bayaran melebihi had yang dibenarkan.')]);
                if ($standalone) {
                    foreach ($data['manual_items'] as $item) {
                        $amount = InvoicePayments::cents($item['amount']);
                        $total += $amount;
                        $items->push((object) ['item_description' => $item['description'], 'quantity' => 1, 'unit' => 'bayaran', 'unit_price' => $amount / 100, 'subtotal' => $amount / 100, 'invoice_no' => '-']);
                    }
                    if ($total > 999999999999) throw ValidationException::withMessages(['manual_items' => __('Jumlah bayaran melebihi had yang dibenarkan.')]);
                } else {
                    // Invoice-linked vouchers retain the source invoice identity.
                    $data['supplier_name'] = $invoices->get($submittedIds[0])->supplier_name;
                }
                $details = collect($data)->only(['issuer_name','issuer_address','issuer_phone','issuer_email','supplier_name','supplier_person','supplier_address','supplier_phone','supplier_email'])->all();
                $details['source_type'] = $standalone ? 'standalone' : 'invoice';
                $details['invoice_numbers'] = $standalone ? [] : $items->pluck('invoice_no')->all();
                $record = collect($data)->only(['voucher_no','voucher_date','payment_method','cheque_no','description','prepared_by','prepared_role','received_by','received_role','supplier_id','supplier_payment_status'])->all();
                $record += ['voucher_type' => 'supplier', 'supplier_quotation_id' => null, 'payee_name' => $data['supplier_name'], 'total_amount' => $total / 100,
                    'supplier_details' => json_encode($details, JSON_UNESCAPED_UNICODE), 'file_path' => $newPath,
                    'file_name' => preg_replace('/[^A-Za-z0-9._-]/', '-', $data['voucher_no']).'.pdf', 'file_mime' => 'application/pdf',
                    'uploaded_by' => $locked?->uploaded_by ?? $request->user()->id, 'created_at' => $locked?->created_at ?? now(), 'updated_at' => now()];
                if (!$locked || $locked->supplier_payment_status !== $data['supplier_payment_status']) {
                    $record['supplier_status_changed_at'] = now(); $record['supplier_status_changed_by'] = $request->user()->id;
                }
                $pdf = Pdf::loadView('pages.baucar-bayaran.supplier-pdf', [
                    'voucher' => (object) ($record + ['description' => null]), 'quotation' => (object) ['quotation_no_supplier' => '', 'supplier_address' => $details['supplier_address'] ?? '', 'supplier_phone' => $details['supplier_phone'] ?? ''],
                    'items' => $items, 'supplierName' => $data['supplier_name'], 'preparedBy' => $data['prepared_by'], 'preparedRole' => $data['prepared_role'],
                    'receivedBy' => $data['received_by'] ?? '', 'receivedRole' => $data['received_role'] ?? '', 'chequeNo' => $data['cheque_no'] ?? null,
                ])->setPaper('a4')->setOption('isRemoteEnabled', false);
                if (!Storage::disk('local')->put($newPath, $pdf->output())) throw new \RuntimeException('PDF could not be saved.');
                if ($id) DB::table('payment_voucher_documents')->where('id', $id)->update($record);
                $voucherId = $id ?? DB::table('payment_voucher_documents')->insertGetId($record);
                DB::table('payment_voucher_supplier_item')->where('payment_voucher_id', $voucherId)->delete();
                DB::table('supplier_invoice_payments')->where('payment_voucher_id', $voucherId)->delete();
                foreach ($items as $item) {
                    $row = (array) $item; unset($row['invoice_no']);
                    DB::table('payment_voucher_supplier_item')->insert($row + ['payment_voucher_id' => $voucherId, 'created_at' => now(), 'updated_at' => now()]);
                }
                foreach ($rows as $row) DB::table('supplier_invoice_payments')->insert($row + ['payment_voucher_id' => $voucherId]);
                return $voucherId;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newPath);
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) throw ValidationException::withMessages(['voucher_no' => __('Nombor baucar sudah digunakan.')]);
            throw $e;
        }
        if ($oldPath) Storage::disk('local')->delete($oldPath);
        return redirect()->route('baucar-bayaran.show', $voucherId)->with('success', __('Baucar dan rekod bayaran berjaya disimpan.'));
    }
}
