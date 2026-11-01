<?php

namespace App\Http\Controllers;

use App\Services\InvoicePayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonitoringLinksController extends Controller
{
    public function paymentStatus(Request $request, int $id)
    {
        $data = $request->validate(['supplier_payment_status' => 'required|in:paid,unpaid']);
        DB::transaction(function () use ($data, $request, $id) {
            $voucher = DB::table('payment_voucher_documents')->where('voucher_type', 'supplier')->where('id', $id)->lockForUpdate()->firstOrFail();
            if ($data['supplier_payment_status'] === 'paid') {
                $recorded = DB::table('supplier_invoice_payments')->where('payment_voucher_id', $id)->get()->sum(fn ($p) => InvoicePayments::cents($p->amount));
                if ($recorded !== InvoicePayments::cents($voucher->total_amount) || $recorded <= 0) {
                    throw ValidationException::withMessages(['supplier_payment_status' => __('Lengkapkan invois dan amaun melalui Edit Baucar sebelum menandakan Dah Bayar.')]);
                }
            }
            if ($voucher->supplier_payment_status !== $data['supplier_payment_status']) {
                DB::table('payment_voucher_documents')->where('id', $id)->update($data + [
                    'supplier_status_changed_at' => now(), 'supplier_status_changed_by' => $request->user()->id, 'updated_at' => now(),
                ]);
            }
        });
        return redirect()->route('baucar-bayaran.show', $id)->with('success', __('Status bayaran pembekal berjaya dikemas kini.'));
    }

    public function voucher(int $id)
    {
        DB::table('payment_voucher_documents')->where('voucher_type', 'supplier')->where('id', $id)->firstOrFail();
        return redirect()->route('baucar-bayaran.supplier.edit', $id);
    }

    public function lo(int $id)
    {
        $lo = DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail();
        $orders = DB::table('purchase_order_master')->where(fn ($q) => $q->whereNull('lo_inden_id')->orWhere('lo_inden_id', $id))->orderByDesc('po_date')->get();
        return view('pages.monitoring.lo', compact('lo', 'orders') + ['title' => __('Rujukan PO kepada LO')]);
    }

    public function saveLo(Request $request, int $id)
    {
        $data = $request->validate(['orders' => 'nullable|array|max:500', 'orders.*' => 'integer|distinct']);
        DB::transaction(function () use ($data, $id) {
            DB::table('lo_inden_master')->where('lo_inden_id', $id)->lockForUpdate()->firstOrFail();
            $ids = $data['orders'] ?? [];
            $orders = DB::table('purchase_order_master')->where(fn ($q) => $q->whereIn('purchase_order_id', $ids)->orWhere('lo_inden_id', $id))->orderBy('purchase_order_id')->lockForUpdate()->get();
            foreach ($ids as $poId) {
                $po = $orders->firstWhere('purchase_order_id', $poId);
                if (!$po || ($po->lo_inden_id && (int) $po->lo_inden_id !== $id)) {
                    throw ValidationException::withMessages(['orders' => __('PO telah dipautkan kepada LO lain atau tidak lagi tersedia.')]);
                }
            }
            DB::table('purchase_order_master')->where('lo_inden_id', $id)->update(['lo_inden_id' => null]);
            DB::table('purchase_order_master')->whereIn('purchase_order_id', $ids)->update(['lo_inden_id' => $id]);
        });
        return redirect()->route('monitoring', ['tab' => 'lo'])->with('success', __('Rujukan PO berjaya disimpan.'));
    }
}
