<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePayments
{
    public static function cents($amount): int { return (int) round((float) $amount * 100); }

    public static function history(int $id) {
        return DB::table('payment_voucher_invoice as p')
            ->join('payment_voucher_documents as v', 'v.id', '=', 'p.payment_voucher_id')
            ->where('p.invoice_id', $id)->orderBy('v.voucher_date')->orderBy('v.id')
            ->get(['v.id', 'v.voucher_no', 'v.voucher_date', 'v.payment_method', 'p.amount']);
    }

    public static function summary(int $id, $total): array {
        $history = self::history($id);
        $paid = $history->sum(fn ($row) => self::cents($row->amount));
        $balance = max(0, self::cents($total) - $paid);
        $pending = $history->contains(fn ($row) => $row->amount === null);
        return ['paid_amount' => $paid / 100, 'balance_amount' => $balance / 100,
            'payment_status' => $pending ? __('Perlu Semakan') : ($balance === 0 ? __('Selesai Dibayar') : ($paid > 0 ? __('Bayaran Sebahagian') : __('Belum Dibayar')))];
    }

    // Call inside a transaction. Lock invoice rows in a stable order to serialize receipts.
    public static function allocate(array $ids, array $amounts, $total, ?int $exclude = null): array {
        sort($ids);
        $invoices = DB::table('invoice_master')->whereIn('invoice_id', $ids)->where('document_kind', 'customer')->orderBy('invoice_id')->lockForUpdate()->get();
        if ($invoices->count() !== count($ids) || $invoices->pluck('customer_id')->unique()->count() > 1) {
            throw ValidationException::withMessages(['invoice_ids' => __('Pilih invois daripada pelanggan yang sama.')]);
        }
        $sum = 0; $rows = [];
        foreach ($invoices as $invoice) {
            $id = $invoice->invoice_id;
            $amount = self::cents($amounts[$id] ?? 0);
            $payments = DB::table('payment_voucher_invoice')->where('invoice_id', $id)->when($exclude, fn ($q) => $q->where('payment_voucher_id', '!=', $exclude))->get();
            if (!$exclude && $payments->contains(fn ($p) => $p->amount === null)) {
                throw ValidationException::withMessages(['invoice_ids' => __('Semak amaun baucar lama untuk invois ini dahulu.')]);
            }
            $paid = $payments->sum(fn ($p) => self::cents($p->amount));
            if ($amount <= 0 || $amount > self::cents($invoice->total_amount) - $paid) {
                throw ValidationException::withMessages(['allocations.'.$id => __('Amaun bayaran mesti melebihi sifar dan tidak melebihi baki invois.')]);
            }
            $sum += $amount;
            $rows[] = ['invoice_id' => $id, 'amount' => $amount / 100];
        }
        if ($sum !== self::cents($total)) {
            throw ValidationException::withMessages(['total_amount' => __('Jumlah pecahan bayaran mesti sama dengan amaun baucar.')]);
        }
        return $rows;
    }
}
