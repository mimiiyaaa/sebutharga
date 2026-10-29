<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvoicePayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $documents = DB::table('payment_voucher_documents')->where('voucher_type', 'customer')->orderByDesc('id')->get();
        $allocations = DB::table('payment_voucher_invoice as link')
            ->join('invoice_master as invoice', 'invoice.invoice_id', '=', 'link.invoice_id')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'invoice.customer_id')
            ->whereIn('link.payment_voucher_id', $documents->pluck('id'))
            ->orderBy('invoice.invoice_no')
            ->orderBy('link.payment_voucher_id')
            ->get([
                'link.invoice_id', 'link.payment_voucher_id', 'link.amount as allocation_amount',
                'invoice.invoice_no', 'invoice.total_amount as invoice_total', 'customer.company_name as customer_name',
            ])
            ->map(function ($allocation) use ($documents) {
                $voucher = $documents->firstWhere('id', $allocation->payment_voucher_id);
                $allocation->voucher_no = $voucher->voucher_no;
                $allocation->voucher_date = $voucher->voucher_date;
                $allocation->payment_method = $voucher->payment_method;
                $allocation->voucher_url = route('baucar-bayaran.show', $voucher->id);
                $allocation->delete_url = route('baucar-bayaran.delete', $voucher->id);
                return $allocation;
            });

        $invoiceGroups = $allocations->groupBy('invoice_id')->map(function ($rows) {
            $first = $rows->first();
            $paidCents = $rows->sum(fn ($row) => $row->allocation_amount === null ? 0 : InvoicePayments::cents($row->allocation_amount));
            $totalCents = InvoicePayments::cents($first->invoice_total);
            $balanceCents = max(0, $totalCents - $paidCents);
            $pending = $rows->contains(fn ($row) => $row->allocation_amount === null);

            return (object) [
                'invoice_no' => $first->invoice_no,
                'customer_name' => $first->customer_name,
                'total_amount' => $totalCents / 100,
                'paid_amount' => $paidCents / 100,
                'balance_amount' => $balanceCents / 100,
                'payment_status' => $pending ? __('Perlu Semakan') : ($balanceCents === 0 ? __('Selesai Dibayar') : ($paidCents > 0 ? __('Bayaran Sebahagian') : __('Belum Dibayar'))),
                'payments' => $rows,
                'search_text' => strtolower(implode(' ', [$first->invoice_no, $first->customer_name, $rows->pluck('voucher_no')->implode(' ')])),
            ];
        })->values();

        $linkedVoucherIds = $allocations->pluck('payment_voucher_id')->unique();
        $unlinkedDocuments = $documents->reject(fn ($document) => $linkedVoucherIds->contains($document->id));


        return view('pages.baucar-bayaran.index', [
            'title' => __('Baucar Bayaran'),
            'invoiceGroups' => $invoiceGroups,
            'unlinkedDocuments' => $unlinkedDocuments,
        ]);
    }

    public function supplier()
    {
        $vouchers = DB::table('payment_voucher_documents as voucher')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'voucher.supplier_id')
            ->leftJoin('pembekal_quotation_master as quotation', 'quotation.supplier_quotation_id', '=', 'voucher.supplier_quotation_id')
            ->where('voucher.voucher_type', 'supplier')
            ->orderByDesc('voucher.id')
            ->get([
                'voucher.id', 'voucher.voucher_no', 'voucher.voucher_date', 'voucher.payment_method', 'voucher.cheque_no', 'voucher.total_amount',
                'supplier.company_name as supplier_name', 'quotation.supplier_quotation_id', 'quotation.quotation_no_supplier',
                'quotation.quotation_title',
            ]);

        return view('pages.baucar-bayaran.supplier-index', [
            'title' => __('Baucar Kepada Pembekal'),
            'vouchers' => $vouchers,
        ]);
    }

    public function supplierCreate()
    {
        $quotations = DB::table('pembekal_quotation_master as quotation')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
            ->orderByDesc('quotation.supplier_quotation_id')
            ->get([
                'quotation.supplier_quotation_id', 'quotation.quotation_no_supplier', 'quotation.quotation_date',
                'quotation.quotation_title', 'quotation.company_name', 'supplier.company_name as supplier_registered_name',
            ]);

        $totals = DB::table('pembekal_quotation_item')
            ->whereIn('supplier_quotation_id', $quotations->pluck('supplier_quotation_id'))
            ->select('supplier_quotation_id', DB::raw('SUM(subtotal) as total_amount'))
            ->groupBy('supplier_quotation_id')
            ->pluck('total_amount', 'supplier_quotation_id');

        $quotations->each(function ($quotation) use ($totals) {
            $quotation->total_amount = (float) ($totals->get($quotation->supplier_quotation_id) ?? 0);
            $quotation->supplier_name = $quotation->supplier_registered_name ?: $quotation->company_name;
        });

        return view('pages.baucar-bayaran.supplier-select-quotation', [
            'title' => __('Pilih Sebut Harga Pembekal'),
            'quotations' => $quotations,
        ]);
    }

    public function supplierForm(Request $request)
    {
        $data = $request->validate([
            'supplier_quotation_id' => ['required', 'integer', 'exists:pembekal_quotation_master,supplier_quotation_id'],
        ]);

        $quotation = DB::table('pembekal_quotation_master as quotation')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
            ->where('quotation.supplier_quotation_id', $data['supplier_quotation_id'])
            ->first([
                'quotation.*', 'supplier.company_name as supplier_registered_name', 'supplier.address as supplier_address',
                'supplier.phone_no as supplier_phone', 'supplier.email as supplier_email',
            ]);
        $quotation->supplier_name = $quotation->supplier_registered_name ?: $quotation->company_name;
        $quotation->total_amount = (float) DB::table('pembekal_quotation_item')
            ->where('supplier_quotation_id', $quotation->supplier_quotation_id)
            ->sum('subtotal');
        $supplierQuotationItems = DB::table('pembekal_quotation_item')
            ->where('supplier_quotation_id', $quotation->supplier_quotation_id)
            ->orderBy('supplier_quotation_item_id')
            ->get();

        return view('pages.baucar-bayaran.supplier', [
            'title' => __('Tambah Baucar Kepada Pembekal'),
            'quotation' => $quotation,
            'supplierQuotationItems' => $supplierQuotationItems,
            'nextVoucherNo' => $this->nextVoucherNumber(),
            'preparedBy' => old('prepared_by', auth()->user()?->name),
            'preparedRole' => old('prepared_role', config('purchase_order.prepared_role')),
            'receivedBy' => old('received_by', $quotation->supplier_name),
            'receivedRole' => old('received_role', 'Tandatangan Penerima'),
            'chequeNo' => old('cheque_no'),
            'initialSupplierItems' => old('items_json') ? json_decode(old('items_json'), true) : $supplierQuotationItems->map(fn ($item, $index) => [
                'id' => $index + 1, 'description' => $item->item_description, 'quantity' => (int) $item->quantity,
                'unit' => $item->unit, 'price' => (float) $item->unit_price,
            ])->values()->all(),
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'supplier_quotation_id' => ['required', 'integer', 'exists:pembekal_quotation_master,supplier_quotation_id'],
            'supplier_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 2)],
            'voucher_no' => ['required', 'string', 'max:100', Rule::unique('payment_voucher_documents', 'voucher_no')],
            'voucher_date' => ['required', 'date_format:Y-m-d'],
            'payment_method' => ['required', 'string', Rule::in(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'])],
            'cheque_no' => ['nullable', 'string', 'max:100', Rule::requiredIf($request->input('payment_method') === 'Cek')],
            'description' => ['nullable', 'string', 'max:5000'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'prepared_by' => ['required', 'string', 'max:255'],
            'prepared_role' => ['required', 'string', 'max:255'],
            'received_by' => ['required', 'string', 'max:255'],
            'received_role' => ['required', 'string', 'max:255'],
            'items_json' => ['required', 'json'],
        ]);
        $items = json_decode($data['items_json'], true);
        validator(['items' => $items], [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array'],
            'items.*.description' => ['required', 'string', 'max:2000'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:1000000', 'decimal:0,2'],
        ])->validate();
        $items = collect($items)->map(fn ($item) => (object) [
            'item_description' => $item['description'],
            'quantity' => (int) $item['quantity'],
            'unit' => $item['unit'],
            'unit_price' => (float) $item['price'],
            'subtotal' => round((int) $item['quantity'] * (float) $item['price'], 2),
        ]);

        $quotation = DB::table('pembekal_quotation_master as quotation')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
            ->where('quotation.supplier_quotation_id', $data['supplier_quotation_id'])
            ->first([
                'quotation.*', 'supplier.company_name as supplier_registered_name', 'supplier.address as supplier_address',
                'supplier.phone_no as supplier_phone', 'supplier.email as supplier_email',
            ]);
        abort_unless($quotation, 404);
        abort_unless((int) $quotation->supplier_id === (int) $data['supplier_id'], 422);
        $supplierName = $quotation->supplier_registered_name ?: $quotation->company_name;
        $items = DB::table('pembekal_quotation_item')
            ->where('supplier_quotation_id', $quotation->supplier_quotation_id)
            ->orderBy('supplier_quotation_item_id')
            ->get();
        $path = 'payment-vouchers/supplier/'.preg_replace('/[^A-Za-z0-9._-]/', '-', $data['voucher_no']).'.pdf';
        $fileName = basename($path);

        try {
            $pdf = Pdf::loadView('pages.baucar-bayaran.supplier-pdf', [
                'voucher' => (object) array_merge($data, ['payee_name' => $supplierName]),
                'quotation' => $quotation,
                'items' => $items,
                'supplierName' => $supplierName,
                'preparedBy' => $data['prepared_by'],
                'preparedRole' => $data['prepared_role'],
                'receivedBy' => $data['received_by'],
                'receivedRole' => $data['received_role'],
                'chequeNo' => $data['cheque_no'] ?? null,
            ])->setPaper('a4')->setOption('isRemoteEnabled', false);
            Storage::disk('local')->put($path, $pdf->output());

            $voucherId = DB::table('payment_voucher_documents')->insertGetId([
                'voucher_no' => $data['voucher_no'],
                'voucher_type' => 'supplier',
                'voucher_date' => $data['voucher_date'],
                'payee_name' => $supplierName,
                'supplier_id' => $data['supplier_id'],
                'supplier_quotation_id' => $data['supplier_quotation_id'],
                'payment_method' => $data['payment_method'],
                'cheque_no' => $data['cheque_no'] ?? null,
                'description' => $data['description'] ?? null,
                'total_amount' => $data['total_amount'],
                'prepared_by' => $data['prepared_by'],
                'prepared_role' => $data['prepared_role'],
                'received_by' => $data['received_by'],
                'received_role' => $data['received_role'],
                'file_path' => $path,
                'file_name' => mb_substr($fileName, 0, 255),
                'file_mime' => 'application/pdf',
                'uploaded_by' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($items as $item) {
                DB::table('payment_voucher_supplier_item')->insert([
                    'payment_voucher_id' => $voucherId,
                    'item_description' => $item->item_description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                throw \Illuminate\Validation\ValidationException::withMessages(['voucher_no' => __('Nombor baucar sudah digunakan.')]);
            }
            throw $exception;
        }

        return redirect()->route('baucar-bayaran.supplier')->with('success', __('Baucar bayaran pembekal berjaya disimpan.'));
    }

    public function create()
    {
        $customerInvoices = DB::table('invoice_master as im')
            ->leftJoin('customer_supplier as cs', 'cs.customer_id', '=', 'im.customer_id')
            ->where('im.document_kind', 'customer')
            ->orderByDesc('im.invoice_date')
            ->orderByDesc('im.invoice_id')
            ->get([
                'im.invoice_id as id',
                'cs.company_name as customer_name',
                'im.invoice_no as document_no',
                'im.invoice_title as document_title',
                'im.invoice_date as document_date',
                'im.total_amount',
            ]);

        $customerInvoices->each(function ($invoice) { foreach (InvoicePayments::summary($invoice->id, $invoice->total_amount) as $key => $value) $invoice->$key = $value; });

        return view('pages.baucar-bayaran.select-invoice', [
            'title' => __('Pilih Invoice Pelanggan'),
            'customerInvoices' => $customerInvoices,
        ]);
    }

    public function form(Request $request)
    {
        $withoutInvoice = $request->boolean('tanpa_invoice');
        if (!$withoutInvoice && !$request->filled('invoice_ids')) { return redirect()->route('baucar-bayaran.create')->with('error', __('Pilih invois pelanggan dahulu.')); }
        if (!$withoutInvoice) {
            $request->validate(['invoice_ids' => 'required|array|min:1', 'invoice_ids.*' => ['integer', 'distinct', Rule::exists('invoice_master', 'invoice_id')->where('document_kind', 'customer')]]);
        }
        $invoiceIds = collect($request->input('invoice_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $customerInvoices = DB::table('invoice_master as im')
            ->leftJoin('customer_supplier as cs', 'cs.customer_id', '=', 'im.customer_id')
            ->where('im.document_kind', 'customer')
            ->whereIn('im.invoice_id', $invoiceIds)
            ->orderByDesc('im.invoice_date')
            ->orderByDesc('im.invoice_id')
            ->get([
                'im.invoice_id as id',
                'cs.company_name as customer_name',
                'im.invoice_no as document_no',
                'im.invoice_title as document_title',
                'im.invoice_date as document_date',
                'im.total_amount',
            ]);

        $customerInvoices->each(function ($invoice) { foreach (InvoicePayments::summary($invoice->id, $invoice->total_amount) as $key => $value) $invoice->$key = $value; });

        return view('pages.baucar-bayaran.form', [
            'title' => __('Tambah Baucar Bayaran'),
            'customerInvoices' => $customerInvoices,
            'selectedInvoiceIds' => $invoiceIds->map(fn ($id) => (string) $id)->all(),
            'withoutInvoice' => $withoutInvoice,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'voucher_no' => ['required', 'string', 'max:100', Rule::unique('payment_voucher_documents', 'voucher_no')],
            'voucher_date' => ['required', 'date_format:Y-m-d'],
            'payee_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', Rule::in(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'invoice_ids' => ['nullable', 'array', 'max:100'],
            'invoice_ids.*' => [
                'integer', 'distinct',
                Rule::exists('invoice_master', 'invoice_id')->where(fn ($query) => $query->where('document_kind', 'customer')),
            ],
            'allocations' => ['nullable', 'array'],
            'allocations.*' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $invoiceIds = collect($data['invoice_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $totalAmount = $data['total_amount'];

        $file = $request->file('document_file');
        $path = $file->store('payment-vouchers', 'local');

        try {
            DB::transaction(function () use ($data, $request, $file, $path, $totalAmount, $invoiceIds) {
                $allocations = $invoiceIds->isEmpty() ? [] : InvoicePayments::allocate($invoiceIds->all(), $data['allocations'] ?? [], $totalAmount);
                $voucherId = DB::table('payment_voucher_documents')->insertGetId([
                    'voucher_no' => $data['voucher_no'],
                    'voucher_type' => 'customer',
                    'voucher_date' => $data['voucher_date'],
                    'payee_name' => $data['payee_name'],
                    'supplier_id' => null,
                    'payment_method' => $data['payment_method'],
                    'description' => $data['description'] ?? null,
                    'total_amount' => $totalAmount,
                    'file_path' => $path,
                    'file_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                    'file_mime' => $file->getMimeType(),
                    'uploaded_by' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($allocations as $allocation) {
                    DB::table('payment_voucher_invoice')->insert($allocation + [
                        'payment_voucher_id' => $voucherId, 'recorded_by' => $request->user()->id,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                throw \Illuminate\Validation\ValidationException::withMessages(['voucher_no' => __('Nombor baucar sudah digunakan.')]);
            }
            throw $exception;
        }

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya disimpan.'));
    }

    public function show(int $id)
    {
        $document = $this->document($id);
        if ($document->voucher_type === 'supplier') {
            $supplierQuotation = DB::table('pembekal_quotation_master as quotation')
                ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
                ->where('quotation.supplier_quotation_id', $document->supplier_quotation_id)
                ->first([
                    'quotation.*', 'supplier.company_name as supplier_registered_name', 'supplier.address as supplier_address',
                    'supplier.phone_no as supplier_phone', 'supplier.email as supplier_email',
                ]);
            $supplierQuotation->supplier_name = $supplierQuotation->supplier_registered_name ?: $supplierQuotation->company_name;
            $supplierQuotationItems = DB::table('pembekal_quotation_item')
                ->where('supplier_quotation_id', $supplierQuotation->supplier_quotation_id)
                ->orderBy('supplier_quotation_item_id')
                ->get();
            $savedItems = DB::table('payment_voucher_supplier_item')
                ->where('payment_voucher_id', $document->id)
                ->orderBy('id')
                ->get();
            if ($savedItems->isNotEmpty()) {
                $supplierQuotationItems = $savedItems;
            }

            return view('pages.baucar-bayaran.supplier-show', [
                'title' => __('Lihat Baucar Bayaran'),
                'document' => $document,
                'supplierQuotation' => $supplierQuotation,
                'supplierQuotationItems' => $supplierQuotationItems,
            ]);
        }

        $invoices = DB::table('payment_voucher_invoice as pvi')
            ->join('invoice_master as im', 'im.invoice_id', '=', 'pvi.invoice_id')
            ->leftJoin('customer_supplier as cs', 'cs.customer_id', '=', 'im.customer_id')
            ->where('pvi.payment_voucher_id', $document->id)
            ->orderBy('im.invoice_date')
            ->get([
                'im.invoice_id as id', 'pvi.amount',
                'im.invoice_no as document_no',
                'im.invoice_title as document_title',
                'cs.company_name as customer_name',
                'im.invoice_date as document_date',
                'im.total_amount',
            ]);

        return view('pages.baucar-bayaran.show', [
            'title' => __('Lihat Baucar Bayaran'),
            'document' => $document,
            'supplierQuotation' => $supplierQuotation,
            'invoices' => $invoices,
        ]);
    }

    public function reconcile(Request $request, int $id)
    {
        $data = $request->validate([
            'allocations' => ['required', 'array'],
            'allocations.*' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
        ]);
        DB::transaction(function () use ($request, $id, $data) {
            $voucher = DB::table('payment_voucher_documents')->where('id', $id)->lockForUpdate()->firstOrFail();
            $links = DB::table('payment_voucher_invoice')->where('payment_voucher_id', $id)->get();
            abort_unless($links->isNotEmpty() && $links->every(fn ($row) => $row->amount === null), 422);
            $rows = InvoicePayments::allocate($links->pluck('invoice_id')->all(), $data['allocations'], $data['total_amount'], $id);
            foreach ($rows as $row) {
                DB::table('payment_voucher_invoice')->where('payment_voucher_id', $id)->where('invoice_id', $row['invoice_id'])->update([
                    'amount' => $row['amount'], 'recorded_by' => $request->user()->id, 'updated_at' => now(),
                ]);
            }
            DB::table('payment_voucher_documents')->where('id', $id)->update(['total_amount' => $data['total_amount'], 'updated_at' => now()]);
        });
        return back()->with('success', __('Bayaran pelanggan berjaya direkodkan.'));
    }

    public function file(Request $request, int $id)
    {
        $document = $this->document($id);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $headers = [
            'Content-Type' => $document->file_mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        return $request->boolean('download')
            ? Storage::disk('local')->download($document->file_path, $document->file_name, $headers)
            : Storage::disk('local')->response($document->file_path, $document->file_name, $headers, 'inline');
    }

    public function destroy(int $id)
    {
        $document = $this->document($id);
        $disk = Storage::disk('local');

        DB::transaction(function () use ($id) {
            DB::table('payment_voucher_documents')->where('id', $id)->lockForUpdate()->firstOrFail();
            $ids = DB::table('payment_voucher_invoice')->where('payment_voucher_id', $id)->pluck('invoice_id');
            DB::table('invoice_master')->whereIn('invoice_id', $ids)->orderBy('invoice_id')->lockForUpdate()->get();
            DB::table('payment_voucher_invoice')->where('payment_voucher_id', $id)->delete();
            DB::table('payment_voucher_supplier_item')->where('payment_voucher_id', $id)->delete();
            DB::table('payment_voucher_documents')->where('id', $id)->delete();
        });
        $disk->delete($document->file_path);

        return redirect()->route($document->voucher_type === 'supplier' ? 'baucar-bayaran.supplier' : 'baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya dipadam.'));
    }

    private function document(int $id): object
    {
        return DB::table('payment_voucher_documents')->where('id', $id)->firstOrFail();
    }

    private function nextVoucherNumber(?string $date = null): string
    {
        $year = $date ? date('Y', strtotime($date)) : now()->format('Y');
        $prefix = "K1R-PV-{$year}-";
        $last = DB::table('payment_voucher_documents')
            ->where('voucher_type', 'supplier')
            ->where('voucher_no', 'like', $prefix.'%')
            ->orderByDesc('voucher_no')
            ->value('voucher_no');
        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
