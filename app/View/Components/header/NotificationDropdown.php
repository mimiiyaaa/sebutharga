<?php

namespace App\View\Components\header;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

class NotificationDropdown extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        foreach (['invoice_master', 'customer_supplier', 'payment_voucher_invoice', 'payment_voucher_documents'] as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return view('components.header.notification-dropdown', ['notifications' => collect(), 'count' => 0, 'unavailable' => true]);
            }
        }
        $today = now('Asia/Kuala_Lumpur')->toDateString();
        $payments = DB::table('payment_voucher_invoice as p')
            ->join('payment_voucher_documents as v', 'v.id', '=', 'p.payment_voucher_id')
            ->whereDate('v.voucher_date', '<=', $today)
            ->groupBy('p.invoice_id')
            ->selectRaw('p.invoice_id, SUM(p.amount) as paid, SUM(CASE WHEN p.amount IS NULL THEN 1 ELSE 0 END) as pending');
        $query = DB::table('invoice_master as i')
            ->leftJoinSub($payments, 'payments', 'payments.invoice_id', '=', 'i.invoice_id')
            ->leftJoin('customer_supplier as c', 'c.customer_id', '=', 'i.customer_id')
            ->where('i.document_kind', 'customer')->whereDate('i.invoice_date', '<=', $today)
            ->where(fn ($q) => $q->whereRaw('i.total_amount > COALESCE(payments.paid, 0)')->orWhere('payments.pending', '>', 0));
        $count = (clone $query)->count();
        $notifications = $query->orderBy('i.invoice_date')->orderBy('i.invoice_id')->limit(8)
            ->get(['i.invoice_id', 'i.invoice_no', 'i.total_amount', 'i.document_data', 'c.company_name', 'payments.paid', 'payments.pending'])
            ->map(function ($row) {
                $snapshot = json_decode($row->document_data ?? '{}', true) ?: [];
                $row->customer = $snapshot['customer_name'] ?? $row->company_name ?? '—';
                $row->balance = max(0, \App\Services\InvoicePayments::cents($row->total_amount) - \App\Services\InvoicePayments::cents($row->paid)) / 100;
                $row->status = $row->pending > 0 ? 'Perlu Semakan' : ($row->paid > 0 ? 'Bayaran Sebahagian' : 'Belum Dibayar');
                return $row;
            });
        return view('components.header.notification-dropdown', compact('notifications', 'count'));
    }
}
