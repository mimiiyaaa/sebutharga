<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $documents = DB::table('payment_voucher_documents')->orderByDesc('id')->get();

        return view('pages.baucar-bayaran.index', [
            'title' => __('Baucar Bayaran'),
            'documents' => $documents,
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

        return view('pages.baucar-bayaran.select-invoice', [
            'title' => __('Pilih Invoice Pelanggan'),
            'customerInvoices' => $customerInvoices,
        ]);
    }

    public function form(Request $request)
    {
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

        return view('pages.baucar-bayaran.form', [
            'title' => __('Tambah Baucar Bayaran'),
            'customerInvoices' => $customerInvoices,
            'selectedInvoiceIds' => $invoiceIds->map(fn ($id) => (string) $id)->all(),
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
            'invoice_ids' => ['nullable', 'array'],
            'invoice_ids.*' => [
                'integer',
                Rule::exists('invoice_master', 'invoice_id')->where(fn ($query) => $query->where('document_kind', 'customer')),
            ],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $invoiceIds = collect($data['invoice_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $selectedInvoices = $invoiceIds->isEmpty()
            ? collect()
            : DB::table('invoice_master')->whereIn('invoice_id', $invoiceIds)->where('document_kind', 'customer')->get();
        $totalAmount = $selectedInvoices->isNotEmpty()
            ? $selectedInvoices->sum(fn ($invoice) => (float) $invoice->total_amount)
            : (float) $data['total_amount'];

        $file = $request->file('document_file');
        $path = $file->store('payment-vouchers', 'local');

        DB::transaction(function () use ($data, $request, $file, $path, $totalAmount, $invoiceIds) {
            $voucherId = DB::table('payment_voucher_documents')->insertGetId([
                'voucher_no' => $data['voucher_no'],
                'voucher_date' => $data['voucher_date'],
                'payee_name' => $data['payee_name'],
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

            if ($invoiceIds->isNotEmpty()) {
                DB::table('payment_voucher_invoice')->insert($invoiceIds->map(fn ($invoiceId) => [
                    'payment_voucher_id' => $voucherId,
                    'invoice_id' => $invoiceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            }
        });

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya disimpan.'));
    }

    public function show(int $id)
    {
        $document = $this->document($id);
        $invoices = DB::table('payment_voucher_invoice as pvi')
            ->join('invoice_master as im', 'im.invoice_id', '=', 'pvi.invoice_id')
            ->leftJoin('customer_supplier as cs', 'cs.customer_id', '=', 'im.customer_id')
            ->where('pvi.payment_voucher_id', $document->id)
            ->orderBy('im.invoice_date')
            ->get([
                'im.invoice_no as document_no',
                'im.invoice_title as document_title',
                'cs.company_name as customer_name',
                'im.invoice_date as document_date',
                'im.total_amount',
            ]);

        return view('pages.baucar-bayaran.show', [
            'title' => __('Lihat Baucar Bayaran'),
            'document' => $document,
            'invoices' => $invoices,
        ]);
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

        if ($disk->exists($document->file_path)) {
            $disk->delete($document->file_path);
        }

        DB::table('payment_voucher_documents')->where('id', $document->id)->delete();
        DB::table('payment_voucher_invoice')->where('payment_voucher_id', $document->id)->delete();

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya dipadam.'));
    }

    private function document(int $id): object
    {
        return DB::table('payment_voucher_documents')->where('id', $id)->firstOrFail();
    }
}
