<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class MonitoringData
{
    public function build(CarbonImmutable $today): array
    {
        $invoices = DB::table('supplier_documents')->where('kind', 'invoice')->whereDate('document_date', '<=', $today->toDateString())->get();
        $allocations = DB::table('supplier_invoice_payments as payment')
            ->join('payment_voucher_documents as voucher', 'voucher.id', '=', 'payment.payment_voucher_id')
            ->where('voucher.voucher_type', 'supplier')
            ->get(['payment.*', 'voucher.voucher_no', 'voucher.voucher_date', 'voucher.supplier_payment_status']);
        $supplierRecords = $invoices->map(function ($invoice) use ($allocations, $today) {
            $linkedVouchers = $allocations->where('supplier_document_id', $invoice->id)->values();
            $history = $linkedVouchers->filter(fn ($payment) => $payment->supplier_payment_status === 'paid' && $payment->voucher_date <= $today->toDateString())->values();
            $paid = $history->sum(fn ($p) => InvoicePayments::cents($p->amount));
            $total = InvoicePayments::cents($invoice->total_amount);
            $balance = max(0, $total - $paid);
            return ['id' => $invoice->id, 'number' => $invoice->document_no, 'party' => $invoice->supplier_name,
                'date' => $invoice->document_date, 'total' => $total / 100, 'paid' => $paid / 100, 'balance' => $balance / 100,
                'status' => $invoice->total_amount === null ? 'Perlu Semakan' : ($balance === 0 ? 'Selesai Dibayar' : ($paid > 0 ? 'Bayaran Sebahagian' : 'Belum Dibayar')),
                'history' => $history, 'linked_vouchers' => $linkedVouchers];
        });
        $voucherAllocations = DB::table('supplier_invoice_payments')->get()->groupBy('payment_voucher_id');
        $vouchers = DB::table('payment_voucher_documents')->where('voucher_type', 'supplier')->whereDate('voucher_date', '<=', $today->toDateString())->get()->map(function ($v) use ($voucherAllocations) {
            $v->standalone = (json_decode($v->supplier_details ?? '{}', true)['source_type'] ?? 'invoice') === 'standalone';
            $v->unallocated = $v->standalone ? 0 : max(0, InvoicePayments::cents($v->total_amount) - $voucherAllocations->get($v->id, collect())->sum(fn ($p) => InvoicePayments::cents($p->amount))) / 100;
            return $v;
        });
        $orders = DB::table('lo_inden_master as lo')->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->whereDate('lo.lo_inden_date', '<=', $today->toDateString())->get(['lo.*', 'customer.company_name']);
        $pos = DB::table('purchase_order_master')->whereDate('po_date', '<=', $today->toDateString())->get();
        $dos = DB::table('delivery_order_master')->where('document_kind', 'customer')->whereDate('do_date', '<=', $today->toDateString())->get();
        $customerInvoices = DB::table('invoice_master')->where('document_kind', 'customer')->whereDate('invoice_date', '<=', $today->toDateString())->get();
        $matches = function ($record, $lo) {
            if ((int) $record->customer_id !== (int) $lo->customer_id) return false;
            $snapshot = json_decode($record->document_data ?? '{}', true) ?: [];
            // Prefer the stored ID; use a reference only for legacy documents without an ID.
            return !empty($snapshot['lo_inden_id']) ? (int) $snapshot['lo_inden_id'] === (int) $lo->lo_inden_id
                : !empty($snapshot['lo_reference']) && $snapshot['lo_reference'] === $lo->lo_inden_no;
        };
        $loRecords = $orders->map(function ($lo) use ($pos, $dos, $customerInvoices, $matches) {
            $lo->pos = $pos->filter(fn ($po) => (int) ($po->lo_inden_id ?? 0) === (int) $lo->lo_inden_id)->values();
            $lo->dos = $dos->filter(fn ($do) => $matches($do, $lo))->values();
            $lo->invoices = $customerInvoices->filter(fn ($invoice) => $matches($invoice, $lo))->values();
            $lo->complete = $lo->pos->isNotEmpty() && $lo->dos->isNotEmpty() && $lo->invoices->isNotEmpty();
            return $lo;
        });
        return compact('supplierRecords', 'vouchers', 'loRecords');
    }
}
