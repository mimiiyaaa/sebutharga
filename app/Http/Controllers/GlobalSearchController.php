<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $results = collect();

        if ($term !== '') {
            $like = '%' . $term . '%';
            $results = $results->merge($this->quotations($like))
                ->merge($this->supplierQuotations($like))
                ->merge($this->purchaseOrders($like))
                ->merge($this->localOrders($like))
                ->merge($this->invoices($like))
                ->merge($this->supplierDocuments($like))
                ->merge($this->deliveryOrders($like))
                ->merge($this->paymentVouchers($like))
                ->merge($this->contacts($like));
        }

        return view('pages.global-search', [
            'title' => 'Carian Global',
            'term' => $term,
            'results' => $results->sortByDesc('date')->values(),
        ]);
    }

    private function quotations(string $like): Collection
    {
        return DB::table('sebutharga_master as quotation')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'quotation.customer_id')
            ->where(function ($query) use ($like) {
                $query->where('quotation.quotation_no', 'like', $like)
                    ->orWhere('quotation.quotation_title', 'like', $like)
                    ->orWhere('customer.company_name', 'like', $like)
                    ->orWhere('customer.customer_name', 'like', $like)
                    ->orWhereExists(function ($item) use ($like) {
                        $item->select(DB::raw(1))->from('sebutharga_detail as detail')
                            ->join('sebutharga_item as quotation_item', 'quotation_item.quotation_detail_id', '=', 'detail.quotation_detail_id')
                            ->whereColumn('detail.quotation_id', 'quotation.quotation_id')
                            ->where('quotation_item.item_description', 'like', $like);
                    });
            })->limit(30)->get([
                'quotation.quotation_id as id', 'quotation.quotation_no as reference',
                'quotation.quotation_title as subject', 'quotation.quotation_date as date',
                'quotation.status_quotation as status', 'customer.company_name as party',
                'quotation.quotation_detail_id as detail_id',
            ])->map(function ($row) {
                return array_merge(
                    $this->result('Sebut Harga', $row, route('sebut-harga.preview', $row->id)),
                    $this->quotationCounters($row->id, $row->detail_id)
                );
            });
    }

    private function supplierQuotations(string $like): Collection
    {
        return DB::table('pembekal_quotation_master as quotation')
            ->where(function ($query) use ($like) {
                $query->where('quotation.quotation_no_supplier', 'like', $like)
                    ->orWhere('quotation.company_name', 'like', $like)
                    ->orWhere('quotation.quotation_title', 'like', $like)
                    ->orWhereExists(function ($item) use ($like) {
                        $item->select(DB::raw(1))->from('pembekal_quotation_item as quotation_item')
                            ->whereColumn('quotation_item.supplier_quotation_id', 'quotation.supplier_quotation_id')
                            ->where('quotation_item.item_description', 'like', $like);
                    });
            })->limit(30)->get([
                'quotation.supplier_quotation_id as id', 'quotation.quotation_no_supplier as reference',
                'quotation.quotation_title as subject', 'quotation.created_at as date',
                'quotation.company_name as party',
            ])->map(fn ($row) => $this->result('Sebut Harga Pembekal', $row, route('sebut-harga-pembekal.show', $row->id)));
    }

    private function purchaseOrders(string $like): Collection
    {
        return DB::table('purchase_order_master as purchase_order')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'purchase_order.customer_id')
            ->leftJoin('sebutharga_master as quotation', 'quotation.quotation_id', '=', 'purchase_order.quotation_id')
            ->where(function ($query) use ($like) {
                $query->where('purchase_order.po_no', 'like', $like)
                    ->orWhere('purchase_order.status_po', 'like', $like)
                    ->orWhere('supplier.company_name', 'like', $like)
                    ->orWhere('quotation.quotation_title', 'like', $like)
                    ->orWhereExists(function ($item) use ($like) {
                        $item->select(DB::raw(1))->from('purchase_order_item as order_item')
                            ->whereColumn('order_item.purchase_order_id', 'purchase_order.purchase_order_id')
                            ->where('order_item.item_description', 'like', $like);
                    });
            })->limit(30)->get([
                'purchase_order.purchase_order_id as id', 'purchase_order.po_no as reference',
                'quotation.quotation_title as subject', 'purchase_order.po_date as date',
                'purchase_order.status_po as status', 'supplier.company_name as party',
            ])->map(fn ($row) => $this->result('Purchase Order', $row, route('purchase-order.show', $row->id)));
    }

    private function localOrders(string $like): Collection
    {
        return DB::table('lo_inden_master as local_order')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'local_order.customer_id')
            ->where(function ($query) use ($like) {
                $query->where('local_order.lo_inden_no', 'like', $like)
                    ->orWhere('customer.company_name', 'like', $like)
                    ->orWhere('customer.customer_name', 'like', $like);
            })->limit(30)->get([
                'local_order.lo_inden_id as id', 'local_order.lo_inden_no as reference',
                'local_order.lo_inden_no as subject', 'local_order.lo_inden_date as date',
                'customer.company_name as party',
            ])->map(fn ($row) => $this->result('Local Order', $row, route('lo.show', $row->id)));
    }

    private function invoices(string $like): Collection
    {
        $hasTitle = Schema::hasColumn('invoice_master', 'invoice_title');
        $query = DB::table('invoice_master as invoice')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'invoice.customer_id')
            ->where(function ($query) use ($like) {
                $query->where('invoice.invoice_no', 'like', $like)
                    ->orWhere('invoice.payment_status', 'like', $like)
                    ->orWhere('customer.company_name', 'like', $like)
                    ->orWhereExists(function ($item) use ($like) {
                        $item->select(DB::raw(1))->from('invoice_item as invoice_item')
                            ->whereColumn('invoice_item.invoice_id', 'invoice.invoice_id')
                            ->where('invoice_item.item_description', 'like', $like);
                    });
            });

        if ($hasTitle) {
            $query->orWhere('invoice.invoice_title', 'like', $like);
        }

        return $query->limit(30)->get([
                'invoice.invoice_id as id', 'invoice.invoice_no as reference',
                ($hasTitle ? 'invoice.invoice_title' : 'invoice.invoice_no') . ' as subject',
                'invoice.invoice_date as date', 'invoice.payment_status as status',
                'customer.company_name as party',
            ])->map(fn ($row) => $this->result(
                'Invoice Pelanggan',
                $row,
                route('invoice.customer.show', $row->id)
            ));
    }

    private function supplierDocuments(string $like): Collection
    {
        $hasTitle = Schema::hasColumn('supplier_documents', 'document_title');
        $query = DB::table('supplier_documents as document')
            ->where(function ($query) use ($like) {
                $query->where('document.document_no', 'like', $like)
                    ->orWhere('document.supplier_name', 'like', $like)
                    ->orWhere('document.po_reference', 'like', $like)
                    ->orWhere('document.source_details', 'like', $like)
                    ->orWhere('document.notes', 'like', $like)
                    ->orWhere('document.items', 'like', $like);
            });

        if ($hasTitle) {
            $query->orWhere('document.document_title', 'like', $like);
        }

        return $query->limit(30)->get([
                'document.id', 'document.document_no as reference',
                ($hasTitle ? 'document.document_title' : 'document.document_no') . ' as subject',
                'document.document_date as date', 'document.supplier_name as party',
                'document.kind',
            ])->map(fn ($row) => $this->result(
                $row->kind === 'invoice' ? 'Invoice Pembekal' : 'DO Pembekal',
                $row,
                route($row->kind === 'invoice' ? 'invoice.supplier.show' : 'delivery-order.supplier.show', $row->id)
            ));
    }

    private function deliveryOrders(string $like): Collection
    {
        return DB::table('delivery_order_master as delivery_order')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'delivery_order.customer_id')
            ->where(function ($query) use ($like) {
                $query->where('delivery_order.do_no', 'like', $like)
                    ->orWhere('delivery_order.delivery_status', 'like', $like)
                    ->orWhere('delivery_order.remarks', 'like', $like)
                    ->orWhere('customer.company_name', 'like', $like)
                    ->orWhereExists(function ($item) use ($like) {
                        $item->select(DB::raw(1))->from('delivery_order_item as delivery_item')
                            ->whereColumn('delivery_item.delivery_order_id', 'delivery_order.delivery_order_id')
                            ->where('delivery_item.item_description', 'like', $like);
                    });
            })->limit(30)->get([
                'delivery_order.delivery_order_id as id', 'delivery_order.do_no as reference',
                'delivery_order.do_no as subject', 'delivery_order.do_date as date',
                'delivery_order.delivery_status as status', 'customer.company_name as party',
            ])->map(fn ($row) => $this->result('DO Pelanggan', $row, route('delivery-order.customer.show', $row->id)));
    }

    private function paymentVouchers(string $like): Collection
    {
        return DB::table('payment_voucher_documents as voucher')
            ->where(function ($query) use ($like) {
                $query->where('voucher.voucher_no', 'like', $like)
                    ->orWhere('voucher.payee_name', 'like', $like)
                    ->orWhere('voucher.description', 'like', $like);
            })->limit(30)->get([
                'voucher.id', 'voucher.voucher_no as reference', 'voucher.description as subject',
                'voucher.voucher_date as date', 'voucher.payee_name as party',
            ])->map(fn ($row) => $this->result('Baucar Bayaran', $row, route('baucar-bayaran.show', $row->id)));
    }

    private function contacts(string $like): Collection
    {
        return DB::table('customer_supplier')
            ->where(function ($query) use ($like) {
                $query->where('customer_code', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone_no', 'like', $like);
            })->limit(30)->get([
                'customer_id as id', 'customer_code as reference', 'company_name as subject',
                'created_at as date', 'customer_name as party', 'jenis_customer as kind',
            ])->map(fn ($row) => $this->result(
                (int) $row->kind === 2 ? 'Pembekal' : 'Pelanggan',
                $row,
                route('contacts.show', [(int) $row->kind === 2 ? 'pembekal' : 'pelanggan', $row->id])
            ));
    }

    private function quotationCounters(int $quotationId, ?int $detailId): array
    {
        $latestDraftNo = (int) DB::table('sebutharga_detail')
            ->where('quotation_id', $quotationId)
            ->max('draft_no');

        $indentCount = 0;
        if (Schema::hasColumn('lo_inden_master', 'quotation_id')) {
            $indentCount = (int) DB::table('lo_inden_master')
                ->where('quotation_id', $quotationId)
                ->count();
        }

        if ($detailId && Schema::hasColumn('lo_inden_master', 'quotation_detail_id')) {
            $indentCount = max($indentCount, (int) DB::table('lo_inden_master')
                ->where('quotation_detail_id', $detailId)
                ->count());
        }

        return [
            'rev' => max(0, $latestDraftNo - 1),
            'indent' => $indentCount,
        ];
    }

    private function result(string $type, object $row, string $url): array
    {
        return [
            'type' => $type,
            'reference' => $row->reference ?? '-',
            'subject' => $row->subject ?: ($row->reference ?? '-'),
            'party' => $row->party ?: '-',
            'date' => $row->date,
            'status' => $row->status ?? null,
            'rev' => $row->rev ?? null,
            'indent' => $row->indent ?? null,
            'url' => $url,
        ];
    }
}
