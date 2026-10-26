<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvoicePayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $documents = DB::table('payment_voucher_documents')->orderByDesc('id')->get();
        $references = DB::table('payment_voucher_invoice as link')
            ->join('invoice_master as invoice', 'invoice.invoice_id', '=', 'link.invoice_id')
            ->whereIn('link.payment_voucher_id', $documents->pluck('id'))
            ->orderBy('invoice.invoice_no')
            ->get(['link.payment_voucher_id', 'invoice.invoice_no'])
            ->groupBy('payment_voucher_id');
        $documents->each(function ($document) use ($references) {
            $document->invoice_numbers = $references->get($document->id, collect())->pluck('invoice_no')->unique()->implode("\n");
        });


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

        $customerInvoices->each(function ($invoice) { foreach (InvoicePayments::summary($invoice->id, $invoice->total_amount) as $key => $value) $invoice->$key = $value; });

        return view('pages.baucar-bayaran.select-invoice', [
            'title' => __('Pilih Invoice Pelanggan'),
            'customerInvoices' => $customerInvoices,
        ]);
    }

    public function form(Request $request)
    {
        if (!$request->filled('invoice_ids')) { return redirect()->route('baucar-bayaran.create')->with('error', __('Pilih invois pelanggan dahulu.')); }
        $request->validate(['invoice_ids' => 'required|array|min:1', 'invoice_ids.*' => ['integer', 'distinct', Rule::exists('invoice_master', 'invoice_id')->where('document_kind', 'customer')]]);
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
            'invoice_ids' => ['required', 'array', 'min:1', 'max:100'],
            'invoice_ids.*' => [
                'integer', 'distinct',
                Rule::exists('invoice_master', 'invoice_id')->where(fn ($query) => $query->where('document_kind', 'customer')),
            ],
            'allocations' => ['required', 'array'],
            'allocations.*' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $invoiceIds = collect($data['invoice_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $totalAmount = $data['total_amount'];

        $file = $request->file('document_file');
        $path = $file->store('payment-vouchers', 'local');

        try {
            DB::transaction(function () use ($data, $request, $file, $path, $totalAmount, $invoiceIds) {
                $allocations = InvoicePayments::allocate($invoiceIds->all(), $data['allocations'], $totalAmount);
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
            DB::table('payment_voucher_documents')->where('id', $id)->delete();
        });
        $disk->delete($document->file_path);

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya dipadam.'));
    }

    private function document(int $id): object
    {
        return DB::table('payment_voucher_documents')->where('id', $id)->firstOrFail();
    }
}
