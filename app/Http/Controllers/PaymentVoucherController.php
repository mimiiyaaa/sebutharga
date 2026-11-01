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

    private function supplierInvoiceReferences(?int $id = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('supplier_invoice_payments')) return collect();
        return DB::table('supplier_invoice_payments as payment')->join('supplier_documents as invoice', 'invoice.id', '=', 'payment.supplier_document_id')
            ->when($id, fn ($q) => $q->where('payment.payment_voucher_id', $id))->orderBy('invoice.id')
            ->get(['payment.payment_voucher_id', 'payment.amount', 'invoice.id', 'invoice.document_no', 'invoice.total_amount']);
    }

    public function supplier()
    {
        $vouchers = DB::table('payment_voucher_documents')->where('voucher_type', 'supplier')->orderByDesc('id')
            ->get()->map(function ($voucher) { $voucher->supplier_name = $voucher->payee_name; return $voucher; });

        $references = $this->supplierInvoiceReferences()->groupBy('payment_voucher_id');
        foreach ($vouchers as $voucher) $voucher->invoice_numbers = $references->get($voucher->id, collect())->pluck('document_no')->implode(', ');
        return view('pages.baucar-bayaran.supplier-index', [
            'title' => __('Baucar Kepada Pembekal'),
            'vouchers' => $vouchers,
        ]);
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
        $supplierQuotation = null;
        if ($document->voucher_type === 'supplier') {
            $supplierQuotation = $document->supplier_quotation_id ? DB::table('pembekal_quotation_master as quotation')
                ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
                ->where('quotation.supplier_quotation_id', $document->supplier_quotation_id)
                ->first([
                    'quotation.*', 'supplier.company_name as supplier_registered_name', 'supplier.address as supplier_address',
                    'supplier.phone_no as supplier_phone', 'supplier.email as supplier_email',
                ]) : null;
            $supplierQuotation ??= (object) ['supplier_quotation_id' => null, 'supplier_registered_name' => null, 'company_name' => $document->payee_name, 'quotation_no_supplier' => null, 'supplier_phone' => null, 'supplier_email' => null, 'supplier_address' => null];
            $details = \App\Services\SupplierVoucherDetails::values($document, DB::table('customer_supplier')->where('customer_id', $document->supplier_id)->first());
            foreach (['supplier_name', 'supplier_address', 'supplier_phone', 'supplier_email'] as $field) $supplierQuotation->$field = $details[$field];
            $supplierQuotationItems = $supplierQuotation->supplier_quotation_id ? DB::table('pembekal_quotation_item')
                ->where('supplier_quotation_id', $supplierQuotation->supplier_quotation_id)
                ->orderBy('supplier_quotation_item_id')
                ->get() : collect();
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
                'supplierQuotationItems' => $supplierQuotationItems, 'details' => $details, 'invoiceReferences' => $this->supplierInvoiceReferences($id),
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

    public function supplierPreview(int $id)
    {
        $document = DB::table('payment_voucher_documents')->where('id', $id)->where('voucher_type', 'supplier')->firstOrFail();
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        $previewHtml = $this->supplierPdfHtml($document);

        return view('pages.baucar-bayaran.supplier-preview', [
            'title' => __('Pratonton Baucar Bayaran'),
            'document' => $document,
            'previewHtml' => $previewHtml,
        ]);
    }

    private function supplierPdfHtml(object $document): ?string
    {
        $id = $document->id;
        $items = DB::table('payment_voucher_supplier_item')->where('payment_voucher_id', $id)->orderBy('id')->get();
        $previewHtml = null;
        if ($items->isNotEmpty()) {
            $quotation = $document->supplier_quotation_id ? DB::table('pembekal_quotation_master as q')
                ->leftJoin('customer_supplier as s', 's.customer_id', '=', 'q.supplier_id')
                ->where('q.supplier_quotation_id', $document->supplier_quotation_id)
                ->first(['q.quotation_no_supplier', 's.address as supplier_address', 's.phone_no as supplier_phone']) : null;
            $quotation ??= (object) ['quotation_no_supplier' => '-', 'supplier_address' => null, 'supplier_phone' => null];
            $details = \App\Services\SupplierVoucherDetails::values($document, DB::table('customer_supplier')->where('customer_id', $document->supplier_id)->first());
            $quotation->supplier_address = $details['supplier_address'];
            $quotation->supplier_phone = $details['supplier_phone'];
            $references = $this->supplierInvoiceReferences($id)->values();
            foreach ($items as $index => $item) $item->invoice_no = $references->get($index)?->document_no ?? '-';
            $previewHtml = view('pages.baucar-bayaran.supplier-pdf', [
                'voucher' => $document, 'quotation' => $quotation, 'items' => $items,
                'supplierName' => $document->payee_name, 'preparedBy' => $document->prepared_by,
                'preparedRole' => $document->prepared_role, 'receivedBy' => $document->received_by,
                'receivedRole' => $document->received_role, 'chequeNo' => $document->cheque_no,
            ])->render();
        }

        return $previewHtml;
    }

    public function supplierPdf(int $id)
    {
        $document = DB::table('payment_voucher_documents')->where('id', $id)->where('voucher_type', 'supplier')->firstOrFail();
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        $html = $this->supplierPdfHtml($document);
        if ($html !== null) {
            return Pdf::loadHTML($html)->setPaper('a4')->setOption('isRemoteEnabled', false)->stream($document->file_name);
        }
        return Storage::disk('local')->response($document->file_path, $document->file_name, [
            'Content-Type' => $document->file_mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function file(Request $request, int $id)
    {
        $document = $this->document($id);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        if ($document->voucher_type === 'supplier' && ($html = $this->supplierPdfHtml($document)) !== null) {
            $pdf = Pdf::loadHTML($html)->setPaper('a4')->setOption('isRemoteEnabled', false);
            return $request->boolean('download') ? $pdf->download($document->file_name) : $pdf->stream($document->file_name);
        }

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
