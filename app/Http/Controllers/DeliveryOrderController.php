<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeliveryOrderController extends Controller
{
    public function create(Request $request)
    {
        $prefix = 'K1R-DO-'.now()->year.'-';
        $last = DB::table('delivery_order_master')->where('do_no', 'like', $prefix.'%')->pluck('do_no')
            ->map(fn ($number) => (int) substr($number, strlen($prefix)))->max() ?? 0;
        return view('pages.delivery-order.create', [
            ...\App\Services\CustomerDocumentLo::defaults($request),
            'title' => __('Cipta DO Kepada Pelanggan'),
            'nextNumber' => $prefix.str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT),
            'localOrders' => \App\Services\CustomerDocumentLo::options(),
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get(),
            'companies' => DB::table('companies')->orderBy('nama_syarikat')->get(),
        ]);
    }

    public function index()
    {
        $orders = DB::table('delivery_order_master')->where('document_kind', 'customer')->orderByDesc('delivery_order_id')->get();
        $localOrders = DB::table('lo_inden_master')
            ->whereIn('lo_inden_no', $orders->map(fn ($order) => data_get(json_decode($order->document_data ?? '{}', true), 'lo_reference'))->filter())
            ->get(['lo_inden_no', 'amount', 'quotation_detail_id'])
            ->keyBy('lo_inden_no');
        $quotationDetails = DB::table('sebutharga_detail')
            ->whereIn('quotation_detail_id', $localOrders->pluck('quotation_detail_id')->filter())
            ->get(['quotation_detail_id', 'quotation_title', 'jumlah_total'])
            ->keyBy('quotation_detail_id');
        $rows = $orders->map(function ($order) use ($localOrders, $quotationDetails) {
            $document = json_decode($order->document_data ?? '{}', true);
            $localOrder = $localOrders->get($document['lo_reference'] ?? null);
            $quotation = $quotationDetails->get($localOrder?->quotation_detail_id);
            return [
                'id' => $order->delivery_order_id, 'document_no' => $order->do_no,
                'lo_no' => $document['lo_reference'] ?? null, 'company_name' => $document['customer_name'] ?? $order->recipient_name,
                'document_date' => $order->do_date,
                'quotation_title' => $quotation?->quotation_title ?? '—',
                'amount' => (float) ($quotation?->jumlah_total ?? $localOrder?->amount ?? 0),
                'preview_url' => route('delivery-order.customer.preview', $order->delivery_order_id),
                    'show_url' => route('delivery-order.customer.show', $order->delivery_order_id),
                    'edit_url' => route('delivery-order.customer.edit', $order->delivery_order_id),
                    'delete_url' => route('delivery-order.customer.delete', $order->delivery_order_id),
            ];
        });
        return view('pages.delivery-order', [
            'title' => __('Senarai DO Kepada Pelanggan'), 'documentType' => __('DO Kepada Pelanggan'),
            'deliveryOrders' => $rows, 'createUrl' => route('delivery-order.customer.create'),
            'partyLabel' => __('Pelanggan'), 'customerQuotationColumns' => true,
        ]);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        \App\Services\CustomerDocumentLo::bind($request);
        $data = $request->validate([
            'lo_inden_id' => 'nullable|integer',
            'do_no' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('delivery_order_master', 'do_no')->ignore($id, 'delivery_order_id')],
            'do_date' => 'required|date_format:Y-m-d',
            'customer_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 1)],
            'issuer_name' => 'required|string|max:255', 'issuer_address' => 'required|string|max:2000',
            'issuer_phone' => 'nullable|string|max:50', 'issuer_email' => 'nullable|email|max:255',
            'customer_name' => 'required|string|max:255', 'customer_address' => 'required|string|max:2000',
            'lo_reference' => 'nullable|string|max:100', 'notes' => 'required|string|max:3000',
            'delivered_by' => 'nullable|string|max:255', 'signature_company' => 'required|string|max:255',
            'recipient_name' => 'nullable|string|max:255', 'received_date' => 'nullable|date_format:Y-m-d|after_or_equal:do_date',
            'items' => 'required|array|min:1|max:100', 'items.*' => 'required|array',
            'items.*.description' => 'required|string|max:2000',
            'items.*.quantity' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'items.*.unit' => 'required|string|max:50', 'items.*.checked' => 'nullable|boolean',
        ]);
        $items = collect($data['items'])->map(fn ($item) => (object) [
            'item_description' => $item['description'], 'quantity' => $item['quantity'], 'unit' => $item['unit'],
            'remarks' => !empty($item['checked']) ? '/' : null,
        ]);
        $document = collect($data)->except(['items', 'do_no', 'do_date', 'customer_id'])->all();
        $order = (object) ['do_no' => $data['do_no'], 'do_date' => $data['do_date']];
        return compact('data', 'items', 'document', 'order');
    }

    public function previewDraft(Request $request)
    {
        return $this->renderPdf($this->validated($request))->stream('pratonton-do.pdf');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        try {
            $id = DB::transaction(function () use ($validated, $request) {
                ['data' => $data, 'order' => $order, 'document' => $document, 'items' => $items] = $validated;
                $id = DB::table('delivery_order_master')->insertGetId([
                    'do_no' => $order->do_no, 'do_date' => $order->do_date, 'customer_id' => $data['customer_id'],
                    'delivery_address' => $data['customer_address'], 'recipient_name' => $data['recipient_name'] ?? null,
                    'remarks' => $data['notes'], 'document_data' => json_encode($document, JSON_UNESCAPED_UNICODE),
                    'document_kind' => 'customer', 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($items as $item) {
                    DB::table('delivery_order_item')->insert((array) $item + ['delivery_order_id' => $id, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                }
                return $id;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['do_no' => __('Nombor DO sudah digunakan. Sila gunakan nombor lain.')]);
        }
        return redirect()->route('delivery-order.customer')->with('success', __('DO berjaya dicipta dan disimpan.'));
    }


    public function show(int $id)
    {
        return view('pages.customer-document-show', $this->saved($id) + [
            'kind' => 'delivery-order', 'title' => __('Maklumat DO'),
        ]);
    }

    public function edit(int $id)
    {
        $saved = $this->saved($id);
        $order = $saved['order'];
        return view('pages.delivery-order.create', [
            'localOrders' => \App\Services\CustomerDocumentLo::options(),
            'title' => __('Edit DO'), 'editId' => $id, 'nextNumber' => $order->do_no,
            'values' => $saved['document'] + ['do_no' => $order->do_no, 'do_date' => $order->do_date, 'customer_id' => $order->customer_id],
            'initialItems' => $saved['items']->map(fn ($item) => [
                'description' => $item->item_description, 'quantity' => $item->quantity, 'unit' => $item->unit,
                'checked' => $item->remarks === '/',
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
                ['data' => $data, 'order' => $order, 'document' => $document, 'items' => $items] = $validated;
                $record = DB::table('delivery_order_master')->where('delivery_order_id', $id)->where('document_kind', 'customer')->lockForUpdate()->first();
                abort_unless($record, 404);
                DB::table('delivery_order_master')->where('delivery_order_id', $id)->update([
                    'do_no' => $order->do_no, 'do_date' => $order->do_date, 'customer_id' => $data['customer_id'],
                    'delivery_address' => $data['customer_address'], 'recipient_name' => $data['recipient_name'] ?? null, 'remarks' => $data['notes'],
                    'document_data' => json_encode(array_replace(json_decode($record->document_data ?? '{}', true) ?: [], $document), JSON_UNESCAPED_UNICODE),
                    'updated_by' => $request->user()->id, 'updated_at' => now(),
                ]);
                DB::table('delivery_order_item')->where('delivery_order_id', $id)->delete();
                foreach ($items as $item) {
                    DB::table('delivery_order_item')->insert((array) $item + ['delivery_order_id' => $id, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                }
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['do_no' => __('Nombor dokumen sudah digunakan. Sila gunakan nombor lain.')]);
        }
        return redirect()->route('delivery-order.customer.show', $id)->with('success', __('DO berjaya dikemas kini.'));
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $record = DB::table('delivery_order_master')->where('delivery_order_id', $id)->where('document_kind', 'customer')->lockForUpdate()->first();
            abort_unless($record, 404);
            DB::table('delivery_order_item')->where('delivery_order_id', $id)->delete();
            DB::table('delivery_order_master')->where('delivery_order_id', $id)->delete();
        });
        return redirect()->route('delivery-order.customer')->with('success', __('DO berjaya dipadam.'));
    }

    private function saved(int $id): array
    {
        $order = DB::table('delivery_order_master')->where('document_kind', 'customer')->where('delivery_order_id', $id)->first();
        abort_unless($order, 404);
        $document = json_decode($order->document_data ?? '{}', true);
        $items = DB::table('delivery_order_item')->where('delivery_order_id', $id)->orderBy('do_item_id')->get();
        return compact('order', 'document', 'items');
    }

    public function preview(int $id)
    {
        return view('pages.delivery-order.preview', $this->saved($id));
    }

    private function renderPdf(array $data)
    {
        return Pdf::loadView('pages.delivery-order.pdf', $data)->setPaper('a4')->setOption('isRemoteEnabled', false);
    }

    public function pdf(Request $request, int $id)
    {
        $data = $this->saved($id);
        $pdf = $this->renderPdf($data);
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', $data['order']->do_no).'.pdf';
        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
