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
                'total' => $totalCents / 100,
                'balance' => $balanceCents / 100,
                'due_date' => $dueDate?->format('Y-m-d'),
                'priority' => $priority,
                'reminder' => $reminder,
                'url' => route('invoice.customer.payments', $invoice->invoice_id),
            ];
        });

        $records = $records->filter(function (array $record) use ($filters) {
            return ($filters['q'] === '' || str_contains(strtolower($record['number'] . ' ' . $record['party'] . ' ' . ($record['reminder'] ?? '')), strtolower($filters['q'])))
                && ($filters['priority'] === '' || $record['priority'] === $filters['priority'])
                && ($filters['from'] === '' || ($record['due_date'] && $record['due_date'] >= $filters['from']))
                && ($filters['to'] === '' || ($record['due_date'] && $record['due_date'] <= $filters['to']));
        })->values();

        $summary = [
            'unpaid' => $records->where('balance', '>', 0)->whereIn('priority', ['Belum Dibayar', 'Bayaran Sebahagian'])->count(),
            'unpaid_total' => $records->where('balance', '>', 0)->whereIn('priority', ['Belum Dibayar', 'Bayaran Sebahagian'])->sum('total'),
            'overdue' => $records->where('priority', 'Sudah Lewat')->count(),
            'overdue_total' => $records->where('priority', 'Sudah Lewat')->sum('total'),
            'upcoming' => $records->where('priority', 'Hampir Jatuh Tempoh')->count(),
            'upcoming_total' => $records->where('priority', 'Hampir Jatuh Tempoh')->sum('total'),
            'review' => $records->where('priority', 'Perlu Semakan')->count(),
            'review_total' => $records->where('priority', 'Perlu Semakan')->sum('total'),
            'paid' => $records->where('priority', 'Selesai Dibayar')->count(),
            'paid_total' => $records->where('priority', 'Selesai Dibayar')->sum('total'),
        ];

        return view('pages.monitoring.index', [
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
