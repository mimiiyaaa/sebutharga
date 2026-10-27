<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardData
{
    public function build(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $cutoff = $end->toDateString();
        $invoices = DB::table('invoice_master as i')->leftJoin('customer_supplier as c', 'c.customer_id', '=', 'i.customer_id')
            ->where('i.document_kind', 'customer')->whereDate('i.invoice_date', '<=', $cutoff)
            ->get(['i.*', 'c.company_name']);
        $receipts = DB::table('payment_voucher_invoice as p')->join('payment_voucher_documents as v', 'v.id', '=', 'p.payment_voucher_id')
            ->join('invoice_master as i', 'i.invoice_id', '=', 'p.invoice_id')
            ->where('i.document_kind', 'customer')->whereDate('v.voucher_date', '<=', $cutoff)
            ->get(['p.invoice_id', 'p.amount', 'v.id', 'v.voucher_no', 'v.voucher_date']);
        $byInvoice = $receipts->groupBy('invoice_id');
        $statuses = ['Selesai Dibayar' => 0, 'Bayaran Sebahagian' => 0, 'Belum Dibayar' => 0, 'Perlu Semakan' => 0];
        $agingAmounts = [0, 0, 0, 0];
        $totalCents = 0; $paidCents = 0;
        $balances = $invoices->map(function ($invoice) use ($byInvoice, $end, &$statuses, &$agingAmounts, &$totalCents, &$paidCents) {
            $payments = $byInvoice->get($invoice->invoice_id, collect());
            $total = InvoicePayments::cents($invoice->total_amount);
            $paid = $payments->sum(fn ($p) => InvoicePayments::cents($p->amount));
            $balance = max(0, $total - $paid);
            $status = $payments->contains(fn ($p) => $p->amount === null) ? 'Perlu Semakan'
                : ($balance === 0 ? 'Selesai Dibayar' : ($paid > 0 ? 'Bayaran Sebahagian' : 'Belum Dibayar'));
            $days = (int) CarbonImmutable::parse($invoice->invoice_date)->startOfDay()->diffInDays($end->startOfDay());
            $bucket = $days <= 30 ? 0 : ($days <= 60 ? 1 : ($days <= 90 ? 2 : 3));
            $agingAmounts[$bucket] += $balance;
            $statuses[$status]++; $totalCents += $total; $paidCents += $paid;
            $snapshot = json_decode($invoice->document_data ?? '{}', true) ?: [];
            return ['id' => $invoice->invoice_id, 'number' => $invoice->invoice_no,
                'customer' => $snapshot['customer_name'] ?? $invoice->company_name ?? '—',
                'total' => $total / 100, 'paid' => $paid / 100, 'balance' => $balance / 100,
                'days' => $days, 'bucket' => $bucket, 'status' => $status,
                'url' => route('invoice.customer.payments', $invoice->invoice_id)];
        })->sortByDesc('days')->values();

        $los = DB::table('lo_inden_master')->whereDate('lo_inden_date', '<=', $cutoff)->get();
        $pos = DB::table('purchase_order_master')->whereDate('po_date', '<=', $cutoff)->get();
        $details = DB::table('sebutharga_detail')->whereDate('quotation_date', '<=', $cutoff)->orderByDesc('quotation_detail_id')->get()->groupBy('quotation_id');
        $masters = DB::table('sebutharga_master')->whereIn('quotation_id', $details->keys())->get()->keyBy('quotation_id');
        $drafts = collect(); $finals = collect();
        foreach ($details as $id => $versions) {
            $master = $masters->get($id);
            if (!$master) continue;
            $active = $versions->firstWhere('quotation_detail_id', $master->quotation_detail_id) ?? $versions->first();
            $row = ['number' => $master->quotation_no, 'description' => $active->quotation_title ?: '—',
                'url' => route('sebut-harga.edit', [$id, 'draft' => $active->quotation_detail_id])];
            if (strtolower(trim($active->status_draft)) !== 'final') $drafts->push($row);
            elseif ($active->sent_at && ($active->status_quotation === null || (int) $active->status_quotation === 1)
                && !$los->contains('quotation_id', $id) && !$los->contains('quotation_detail_id', $active->quotation_detail_id)) $finals->push($row);
        }
        $deliveries = DB::table('delivery_order_master')->where('document_kind', 'customer')->whereDate('do_date', '<=', $cutoff)->get(['document_data', 'customer_id']);
        $hasLo = function ($records, $lo) {
            return $records->contains(function ($record) use ($lo) {
                $data = json_decode($record->document_data ?? '{}', true) ?: [];
                return (int) $record->customer_id === (int) $lo->customer_id
                    && ((isset($data['lo_inden_id']) && (int) $data['lo_inden_id'] === (int) $lo->lo_inden_id)
                        || (!empty($data['lo_reference']) && $data['lo_reference'] === $lo->lo_inden_no));
            });
        };
        $loRow = fn ($lo) => ['number' => $lo->lo_inden_no, 'description' => $lo->lo_inden_date, 'url' => route('lo.show', $lo->lo_inden_id)];
        $pending = $receipts->whereNull('amount')->unique('id')->map(fn ($p) => ['number' => $p->voucher_no, 'description' => $p->voucher_date, 'url' => route('baucar-bayaran.show', $p->id)])->values();
        $groups = [
            'drafts' => $drafts, 'finals' => $finals,
            'lo' => $los->map($loRow),
            'po' => $pos->map(fn ($po) => ['number' => $po->po_no, 'description' => $po->po_date, 'url' => route('purchase-order.show', $po->purchase_order_id)]),
            'missing-do' => $los->reject(fn ($lo) => $hasLo($deliveries, $lo))->map($loRow)->values(),
            'missing-invoice' => $los->reject(fn ($lo) => $hasLo($invoices, $lo))->map($loRow)->values(),
            'review' => $pending,
        ];
        $metrics = [['Sebut Harga Draf', $drafts->count(), 'Versi aktif belum dimuktamadkan', 'Draf', 'drafts'],
            ['Final Menunggu LO', $finals->count(), 'Telah dihantar, belum menerima LO', 'Final', 'finals'],
            ['Local Order', $los->count(), 'Pesanan daripada pelanggan', 'LO', 'lo'],
            ['Purchase Order', $pos->count(), 'Pesanan kepada pembekal', 'PO', 'po']];
        $actions = [['Final menunggu LO', $finals->count(), 'Susuli pesanan pelanggan', 'finals'],
            ['LO belum ada DO', $groups['missing-do']->count(), 'Semak dokumen penghantaran', 'missing-do'],
            ['LO belum ada invois', $groups['missing-invoice']->count(), 'Semak invois pelanggan', 'missing-invoice'],
            ['Baucar perlu semakan', $pending->count(), 'Sahkan amaun bayaran lama', 'review']];
        $months = []; $invoiced = []; $received = [];
        for ($month = $start->startOfMonth(); $month <= $end; $month = $month->addMonth()) {
            $key = $month->format('Y-m');
            $months[] = $month->format('m/Y');
            $invoiced[] = $invoices->filter(fn ($i) => substr($i->invoice_date, 0, 7) === $key && $i->invoice_date >= $start->toDateString())->sum(fn ($i) => InvoicePayments::cents($i->total_amount)) / 100000;
            $received[] = $receipts->filter(fn ($p) => substr($p->voucher_date, 0, 7) === $key && $p->voucher_date >= $start->toDateString())->sum(fn ($p) => InvoicePayments::cents($p->amount)) / 100000;
        }
        $aging = collect(['0–30 hari', '31–60 hari', '61–90 hari', 'Lebih 90 hari'])->map(fn ($label, $index) => [$label, $agingAmounts[$index] / 100, max($agingAmounts) > 0 ? $agingAmounts[$index] / max($agingAmounts) * 280 : 0]);
        $financial = [['Jumlah Invois', $totalCents / 100, 'Invois pelanggan hingga tarikh akhir'],
            ['Bayaran Diperuntukkan', $paidCents / 100, 'Bayaran disahkan untuk invois di atas'],
            ['Baki Belum Dibayar', array_sum($agingAmounts) / 100, 'Termasuk bayaran sebahagian']];
        return compact('metrics', 'actions', 'groups', 'months', 'invoiced', 'received', 'aging', 'financial', 'statuses', 'balances')
            + ['invoiceCount' => $invoices->count(), 'pendingCount' => $pending->count(), 'chartMax' => max(1, ceil(max([...$invoiced, ...$received, 0]) / 5) * 5)];
    }
}
