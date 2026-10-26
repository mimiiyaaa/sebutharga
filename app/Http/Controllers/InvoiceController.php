<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\InvoicePayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        $prefix = 'K1R-IV-'.now()->year.'-';
        $last = DB::table('invoice_master')->where('invoice_no', 'like', $prefix.'%')->pluck('invoice_no')
            ->map(fn ($number) => (int) substr($number, strlen($prefix)))->max() ?? 0;

        return view('pages.invoice.create', [
            ...\App\Services\CustomerDocumentLo::defaults($request),
            'title' => __('Cipta Invois Kepada Pelanggan'),
            'nextNumber' => $prefix.str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT),
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get(),
            'companies' => DB::table('companies')->orderBy('nama_syarikat')->get(),
        ]);
    }

    public function index()
    {
        $records = DB::table('invoice_master')->where('document_kind', 'customer')->orderByDesc('invoice_id')->get();
        $localOrders = DB::table('lo_inden_master')
            ->whereIn('lo_inden_no', $records->map(fn ($invoice) => data_get(json_decode($invoice->document_data ?? '{}', true), 'lo_reference'))->filter())
            ->get(['lo_inden_no', 'amount', 'quotation_detail_id'])->keyBy('lo_inden_no');
        $quotationDetails = DB::table('sebutharga_detail')
            ->whereIn('quotation_detail_id', $localOrders->pluck('quotation_detail_id')->filter())
            ->get(['quotation_detail_id', 'quotation_title', 'jumlah_total'])->keyBy('quotation_detail_id');
        $invoices = $records->map(function ($invoice) use ($localOrders, $quotationDetails) {
                $document = json_decode($invoice->document_data ?? '{}', true);
                $localOrder = $localOrders->get($document['lo_reference'] ?? null);
                $quotation = $quotationDetails->get($localOrder?->quotation_detail_id);
                return [
                    'id' => $invoice->invoice_id,
                    'document_no' => $invoice->invoice_no,
                    'invoice_title' => $invoice->invoice_title,
                    'lo_no' => $document['lo_reference'] ?? null,
                    'company_name' => $document['customer_name'] ?? '',
                    'document_date' => $invoice->invoice_date,
                    'quotation_title' => $quotation?->quotation_title ?? '—',
                    'amount' => (float) $invoice->total_amount,
                    ...InvoicePayments::summary($invoice->invoice_id, $invoice->total_amount),
                    'preview_url' => route('invoice.customer.preview', $invoice->invoice_id),
                    'show_url' => route('invoice.customer.show', $invoice->invoice_id),
                    'payment_url' => route('invoice.customer.payments', $invoice->invoice_id),
                    'edit_url' => route('invoice.customer.edit', $invoice->invoice_id),
                    'delete_url' => route('invoice.customer.delete', $invoice->invoice_id),
                ];
            });
        return view('pages.invoice', [
            'title' => __('Invoice Kepada Pelanggan'),
            'documentType' => __('Invoice Kepada Pelanggan'),
            'invoices' => $invoices,
            'createUrl' => route('invoice.customer.create'),
            'partyLabel' => __('Pelanggan'),
            'customerQuotationColumns' => true,
        ]);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        \App\Services\CustomerDocumentLo::bind($request);
        $data = $request->validate([
            'lo_inden_id' => 'nullable|integer',
            'invoice_no' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('invoice_master', 'invoice_no')->ignore($id, 'invoice_id')],
            'invoice_date' => 'required|date_format:Y-m-d',
            'invoice_title' => 'nullable|string|max:255',
            'lo_reference' => 'nullable|string|max:100',
            'customer_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 1)],
            'issuer_name' => 'required|string|max:255', 'issuer_address' => 'required|string|max:2000',
            'issuer_phone' => 'nullable|string|max:50', 'issuer_email' => 'nullable|email|max:255',
            'customer_name' => 'required|string|max:255', 'customer_address' => 'required|string|max:2000',
            'bank_name' => 'required|string|max:255', 'account_name' => 'required|string|max:255', 'account_no' => 'required|string|max:100',
            'payment_instructions' => 'required|string|max:3000',
            'prepared_by' => 'nullable|string|max:255', 'prepared_role' => 'required|string|max:255',
            'signature_company' => 'sometimes|required|string|max:255',
            'items' => 'required|array|min:1|max:100', 'items.*' => 'required|array',
            'items.*.description' => 'required|string|max:2000', 'items.*.quantity' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'items.*.unit' => 'required|string|max:50', 'items.*.price' => 'required|numeric|min:0|max:1000000|decimal:0,2',
        ]);
        $items = collect($data['items'])->map(fn ($item) => (object) [
            'item_description' => $item['description'], 'quantity' => $item['quantity'], 'unit' => $item['unit'],
            'unit_price' => $item['price'], 'subtotal' => round($item['quantity'] * $item['price'], 2),
        ]);
        $total = round($items->sum('subtotal'), 2);
        if ($total > 9999999999.99) {
            throw ValidationException::withMessages(['items' => __('Jumlah invois melebihi had yang dibenarkan.')]);
        }
        $document = collect($data)->except(['items', 'invoice_no', 'invoice_date', 'customer_id'])->all();
        $document['signature_company'] = $data['signature_company'] ?? $data['issuer_name'];
        $invoice = (object) ['invoice_no' => $data['invoice_no'], 'invoice_date' => $data['invoice_date'], 'total_amount' => $total];
        return compact('data', 'items', 'document', 'invoice');
    }

    public function previewDraft(Request $request)
    {
        return $this->renderPdf($this->validated($request))->stream('pratonton-invois.pdf');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        try {
            $id = DB::transaction(function () use ($validated, $request) {
                ['data' => $data, 'invoice' => $invoice, 'document' => $document, 'items' => $items] = $validated;
                $id = DB::table('invoice_master')->insertGetId([
                    'invoice_no' => $invoice->invoice_no, 'invoice_date' => $invoice->invoice_date, 'invoice_title' => $data['invoice_title'] ?? null,
                    'customer_id' => $data['customer_id'], 'subtotal' => $invoice->total_amount, 'total_amount' => $invoice->total_amount,
                    'document_data' => json_encode($document, JSON_UNESCAPED_UNICODE), 'document_kind' => 'customer',
                    'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($items as $item) {
                    DB::table('invoice_item')->insert((array) $item + ['invoice_id' => $id, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                }
                return $id;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['invoice_no' => __('Nombor invois sudah digunakan. Sila gunakan nombor lain.')]);
        }
        return redirect()->route('invoice.customer')->with('success', __('Invois berjaya dicipta dan disimpan.'));
    }


    public function show(int $id)
    {
        return view('pages.customer-document-show', $this->saved($id) + [
            'kind' => 'invoice', 'title' => __('Maklumat Invois'),
        ]);
    }

    public function payments(int $id)
    {
        return view('pages.invoice.payments', $this->saved($id) + [
            'payments' => InvoicePayments::history($id),
        ]);
    }

    public function edit(int $id)
    {
        $saved = $this->saved($id);
        $invoice = $saved['invoice'];
        return view('pages.invoice.create', [
            'localOrders' => \App\Services\CustomerDocumentLo::options(),
            'title' => __('Edit Invois'), 'editId' => $id, 'nextNumber' => $invoice->invoice_no,
            'values' => $saved['document'] + ['invoice_no' => $invoice->invoice_no, 'invoice_date' => $invoice->invoice_date, 'invoice_title' => $invoice->invoice_title, 'customer_id' => $invoice->customer_id],
            'initialItems' => $saved['items']->map(fn ($item) => [
                'description' => $item->item_description, 'quantity' => $item->quantity, 'unit' => $item->unit,
                'price' => $item->unit_price,
            ])->all(),
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get(),
            'companies' => DB::table('companies')->orderBy('nama_syarikat')->get(),
        ]);
    }

    public function previewEdit(Request $request, int $id)
    {
        $this->saved($id);
        return $this->renderPdf($this->validated($request, $id))->stream('pratonton.pdf');
    }

    public function update(Request $request, int $id)
    {
        $this->saved($id);
        $validated = $this->validated($request, $id);
        try {
            DB::transaction(function () use ($validated, $request, $id) {
                ['data' => $data, 'invoice' => $invoice, 'document' => $document, 'items' => $items] = $validated;
                $record = DB::table('invoice_master')->where('invoice_id', $id)->where('document_kind', 'customer')->lockForUpdate()->first();
                abort_unless($record, 404);
                $payments = InvoicePayments::history($id);
                if ($payments->isNotEmpty() && ((int) $record->customer_id !== (int) $data['customer_id'] || InvoicePayments::cents($record->total_amount) !== InvoicePayments::cents($invoice->total_amount))) {
                    throw ValidationException::withMessages(['items' => __('Invois mempunyai rekod baucar. Pelanggan dan jumlah invois tidak boleh ditukar.')]);
                }

                DB::table('invoice_master')->where('invoice_id', $id)->update([
                    'invoice_no' => $invoice->invoice_no, 'invoice_date' => $invoice->invoice_date, 'invoice_title' => $data['invoice_title'] ?? null, 'customer_id' => $data['customer_id'],
                    'subtotal' => $invoice->total_amount, 'total_amount' => $invoice->total_amount,
                    'document_data' => json_encode(array_replace(json_decode($record->document_data ?? '{}', true) ?: [], $document), JSON_UNESCAPED_UNICODE),
                    'updated_by' => $request->user()->id, 'updated_at' => now(),
                ]);
                DB::table('invoice_item')->where('invoice_id', $id)->delete();
                foreach ($items as $item) {
                    DB::table('invoice_item')->insert((array) $item + ['invoice_id' => $id, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                }
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['invoice_no' => __('Nombor dokumen sudah digunakan. Sila gunakan nombor lain.')]);
        }
        return redirect()->route('invoice.customer.show', $id)->with('success', __('Invois berjaya dikemas kini.'));
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $record = DB::table('invoice_master')->where('invoice_id', $id)->where('document_kind', 'customer')->lockForUpdate()->first();
            abort_unless($record, 404);
            if (InvoicePayments::history($id)->isNotEmpty()) {
                throw ValidationException::withMessages(['document' => __('Invois mempunyai rekod bayaran. Padam baucar berkaitan dahulu jika rekod tersilap.')]);
            }
            if (DB::table('delivery_order_master')->where('invoice_id', $id)->exists()) {
                throw ValidationException::withMessages(['document' => __('Invois ini digunakan oleh DO. Sila padam DO berkaitan terlebih dahulu.')]);
            }
            DB::table('invoice_item')->where('invoice_id', $id)->delete();
            DB::table('invoice_master')->where('invoice_id', $id)->delete();
        });
        return redirect()->route('invoice.customer')->with('success', __('Invois berjaya dipadam.'));
    }

    private function saved(int $id): array
    {
        $invoice = DB::table('invoice_master')->where('document_kind', 'customer')->where('invoice_id', $id)->first();
        abort_unless($invoice, 404);
        $document = json_decode($invoice->document_data ?? '{}', true);
        $items = DB::table('invoice_item')->where('invoice_id', $id)->orderBy('invoice_item_id')->get();
        return compact('invoice', 'document', 'items');
    }

    public function preview(int $id)
    {
        return view('pages.invoice.preview', $this->saved($id));
    }

    private function renderPdf(array $data)
    {
        return Pdf::loadView('pages.invoice.pdf', $data)->setPaper('a4')->setOption('isRemoteEnabled', false);
    }

    public function pdf(Request $request, int $id)
    {
        $data = $this->saved($id);
        $pdf = $this->renderPdf($data);
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', $data['invoice']->invoice_no).'.pdf';
        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
