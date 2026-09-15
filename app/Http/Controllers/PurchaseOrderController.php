<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    private function finalQuotations()
    {
        return DB::table('sebutharga_master as master')
            ->join('sebutharga_detail as detail', 'detail.quotation_id', '=', 'master.quotation_id')
            ->join('customer_supplier as customer', 'customer.customer_id', '=', 'master.customer_id')
            ->where('customer.jenis_customer', 1)
            ->whereRaw('LOWER(TRIM(detail.status_draft)) = ?', ['final'])
            ->orderByDesc('master.quotation_date')
            ->orderByDesc('master.quotation_id')
            ->orderByDesc('detail.draft_no')
            ->orderByDesc('detail.quotation_detail_id')
            ->select('master.customer_id', 'customer.company_name', 'master.quotation_id', 'master.quotation_no',
                'master.quotation_title', 'master.quotation_date', 'detail.quotation_detail_id', 'detail.draft_no');
    }

    public function selectQuotation()
    {
        return view('pages.purchase-order.select-quotation', [
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')->get(['customer_id', 'company_name']),
            'quotations' => $this->finalQuotations()->get()->unique('customer_id')->values(),
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 1)],
        ]);
        $quotation = $this->finalQuotations()->where('master.customer_id', $validated['customer_id'])->first();
        if (!$quotation) {
            return redirect()->route('purchase-order.create')->withErrors(['customer_id' => __('Pelanggan ini belum mempunyai sebutharga Final.')]);
        }

        return view('pages.purchase-order.create', [
            'title' => 'Tambah PO',
            'quotation' => $quotation,
            'suppliers' => DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')
                ->get(['customer_id', 'customer_name', 'company_name', 'address', 'phone_no', 'email', 'reference_no']),
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')
                ->get(['customer_id', 'customer_name', 'company_name', 'address', 'phone_no']),
        ]);
    }
}
