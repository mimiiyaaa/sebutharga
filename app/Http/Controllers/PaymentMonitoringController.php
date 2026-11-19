<?php

namespace App\Http\Controllers;

use App\Services\InvoicePayments;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['voucher_source' => 'nullable|in:standalone,invoice']);
        $request->validate(['q' => 'nullable|string|max:200', 'priority' => 'nullable|string|max:60', 'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from', 'tab' => 'nullable|in:customer,supplier,lo']);
        $now = CarbonImmutable::now('Asia/Kuala_Lumpur');
        $today = $now->startOfDay();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'priority' => trim((string) $request->query('priority', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
        ];
        $invoices = DB::table('invoice_master as invoice')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'invoice.customer_id')
            ->where('invoice.document_kind', 'customer')
            ->whereDate('invoice.invoice_date', '<=', $today->toDateString())
            ->get(['invoice.*', 'customer.company_name']);

        $payments = DB::table('payment_voucher_invoice as allocation')
            ->join('payment_voucher_documents as voucher', 'voucher.id', '=', 'allocation.payment_voucher_id')
            ->whereDate('voucher.voucher_date', '<=', $today->toDateString())
            ->get(['allocation.invoice_id', 'allocation.amount']);
        $paymentsByInvoice = $payments->groupBy('invoice_id');

        $records = $invoices->map(function ($invoice) use ($paymentsByInvoice, $today) {
            $invoicePayments = $paymentsByInvoice->get($invoice->invoice_id, collect());
            $totalCents = InvoicePayments::cents($invoice->total_amount);
            $paidCents = $invoicePayments->sum(fn ($payment) => InvoicePayments::cents($payment->amount));
            $balanceCents = max(0, $totalCents - $paidCents);
            $pending = $invoicePayments->contains(fn ($payment) => $payment->amount === null);
            $dueDate = !empty($invoice->due_date) ? CarbonImmutable::parse($invoice->due_date)->startOfDay() : null;
            $daysUntilDue = $dueDate ? $today->diffInDays($dueDate, false) : null;

            if ($pending) {
                $priority = 'Perlu Semakan';
                $reminder = 'Baucar bayaran belum disahkan';
            } elseif ($balanceCents <= 0) {
                $priority = 'Selesai Dibayar';
                $reminder = null;
            } elseif ($dueDate && $daysUntilDue < 0) {
                $priority = 'Sudah Lewat';
                $reminder = 'Invoice sudah melepasi tarikh akhir bayaran';
            } elseif ($dueDate && $daysUntilDue <= 7) {
                $priority = 'Hampir Jatuh Tempoh';
                $reminder = 'Tarikh akhir bayaran dalam 7 hari';
            } else {
                $priority = 'Belum Dibayar';
                $reminder = 'Menunggu bayaran';
            }

            $snapshot = json_decode($invoice->document_data ?? '{}', true) ?: [];
            return [
                'invoice_id' => $invoice->invoice_id,
                'number' => $invoice->invoice_no,
                'party' => $snapshot['customer_name'] ?? ($invoice->company_name ?: '—'),
                'date' => $invoice->invoice_date,
                'total' => $totalCents / 100,
                'paid' => $paidCents / 100,
                'status' => $pending ? 'Perlu Semakan' : ($balanceCents === 0 ? 'Selesai Dibayar' : ($paidCents > 0 ? 'Bayaran Sebahagian' : 'Belum Dibayar')),
                'balance' => $balanceCents / 100,
                'due_date' => $dueDate?->format('Y-m-d'),
                'priority' => $priority,
                'reminder' => $reminder,
                'url' => route('invoice.customer.payments', $invoice->invoice_id),
            ];
        });

        $records = $records->filter(function (array $record) use ($filters) {
            return ($filters['q'] === '' || str_contains(strtolower($record['number'] . ' ' . $record['party'] . ' ' . ($record['reminder'] ?? '')), strtolower($filters['q'])))
                && ($filters['priority'] === '' || (in_array($filters['priority'], ['Sudah Lewat', 'Hampir Jatuh Tempoh']) ? $record['priority'] === $filters['priority'] : $record['status'] === $filters['priority']))
                && ($filters['from'] === '' || $record['date'] >= $filters['from'])
                && ($filters['to'] === '' || $record['date'] <= $filters['to']);
        })->values();

        $summary = [
            'unpaid' => $records->where('balance', '>', 0)->whereIn('priority', ['Belum Dibayar', 'Bayaran Sebahagian'])->count(),
            'unpaid_total' => $records->where('balance', '>', 0)->whereIn('priority', ['Belum Dibayar', 'Bayaran Sebahagian'])->sum('balance'),
            'overdue' => $records->where('priority', 'Sudah Lewat')->count(),
            'overdue_total' => $records->where('priority', 'Sudah Lewat')->sum('balance'),
            'upcoming' => $records->where('priority', 'Hampir Jatuh Tempoh')->count(),
            'upcoming_total' => $records->where('priority', 'Hampir Jatuh Tempoh')->sum('balance'),
            'review' => $records->where('priority', 'Perlu Semakan')->count(),
            'review_total' => $records->where('priority', 'Perlu Semakan')->sum('balance'),
            'paid' => $records->where('priority', 'Selesai Dibayar')->count(),
            'paid_total' => $records->where('priority', 'Selesai Dibayar')->sum('paid'),
        ];

        $additional = (new \App\Services\MonitoringData())->build($today);
        $additional['vouchers'] = $additional['vouchers']->filter(fn ($voucher) => match ($request->query('voucher_source')) {
            'standalone' => $voucher->standalone,
            'invoice' => !$voucher->standalone,
            default => true,
        })->values();
        $additional['supplierRecords'] = $additional['supplierRecords']->filter(fn ($r) =>
            ($filters['q'] === '' || str_contains(strtolower($r['number'].' '.$r['party']), strtolower($filters['q'])))
            && ($filters['priority'] === '' || $r['status'] === $filters['priority'])
            && ($filters['from'] === '' || $r['date'] >= $filters['from'])
            && ($filters['to'] === '' || $r['date'] <= $filters['to']))->values();
        $additional['loRecords'] = $additional['loRecords']->filter(fn ($lo) =>
            ($filters['q'] === '' || str_contains(strtolower($lo->lo_inden_no.' '.$lo->company_name), strtolower($filters['q'])))
            && ($filters['from'] === '' || $lo->lo_inden_date >= $filters['from'])
            && ($filters['to'] === '' || $lo->lo_inden_date <= $filters['to'])
            && match ($filters['priority']) {
                'complete' => $lo->complete, 'missing-po' => $lo->pos->isEmpty(),
                'missing-do' => $lo->dos->isEmpty(), 'missing-invoice' => $lo->invoices->isEmpty(), default => true,
            })->values();
        return view('pages.monitoring.index', $additional + [
            'tab' => $request->query('tab', 'customer'),
            'customerRecords' => $records,
            'customerTotals' => ['total' => $records->sum('total'), 'paid' => $records->sum('paid'), 'balance' => $records->sum('balance')],
            'title' => 'Pemantauan',
            'monitoring' => [
                'summary' => $summary,
                'records' => $records->whereNotNull('reminder')->sortBy(function ($record) {
                    return [$record['priority'] === 'Sudah Lewat' ? 0 : 1, $record['due_date'] ?: '9999-12-31'];
                })->values(),
                'filters' => $filters,
                'updated_at' => $now->format('d/m/Y H:i'),
            ],
        ]);
    }
}
