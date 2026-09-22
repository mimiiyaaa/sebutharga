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
            ->whereNotNull('detail.sent_at')
            ->where('detail.status_quotation', 1)
            ->whereColumn('detail.quotation_detail_id', 'master.quotation_detail_id')
            ->orderByDesc('master.quotation_date')
            ->orderByDesc('master.quotation_id')
            ->orderByDesc('detail.draft_no')
            ->orderByDesc('detail.quotation_detail_id')
            ->select('master.customer_id', 'customer.company_name', 'master.quotation_id', 'master.quotation_no',
                'master.quotation_title', 'master.quotation_date', 'detail.quotation_detail_id', 'detail.draft_no');
    }

    public function selectQuotation()
    {
        $quotations = $this->finalQuotations()->get();
        return view('pages.purchase-order.select-quotation', [
            'quotations' => $quotations,
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'quotation_detail_id' => ['required', 'integer'],
        ]);
        $quotation = $this->finalQuotations()->where('detail.quotation_detail_id', $validated['quotation_detail_id'])->first();
        if (!$quotation) {
            return redirect()->route('purchase-order.create')->withErrors(['quotation_detail_id' => __('Sila pilih sebut harga Final yang dipersetujui.')]);
        }

        // Sebut harga hanya digunakan sebagai rujukan untuk PO. Item PO baharu
        // bermula kosong supaya pengguna boleh mengisinya sendiri.
        $initialItems = [];

        return view('pages.purchase-order.create', [
            'title' => 'Tambah PO',
            'quotation' => $quotation,
            'initialItems' => $initialItems,
            'nextPoNo' => $this->nextPoNumber(),
            'suppliers' => DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')
                ->get(['customer_id', 'customer_name', 'company_name', 'address', 'phone_no', 'email', 'reference_no']),
            'customers' => DB::table('customer_supplier')->where('jenis_customer', 1)->orderBy('company_name')
                ->get(['customer_id', 'customer_name', 'company_name', 'address', 'phone_no']),
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $order = DB::table('purchase_order_master')->where('purchase_order_id', $id)->first();
        abort_unless($order, 404);
        $items = DB::table('purchase_order_item')->where('purchase_order_id', $id)->orderBy('po_item_id')
            ->get()->map(fn ($item, $index) => ['id'=>$index+1, 'description'=>$item->item_description, 'quantity'=>$item->quantity, 'unit'=>$item->unit, 'price'=>$item->unit_price])->all();
        return view('pages.purchase-order.edit', [
            'order'=>$order, 'items'=>$items,
            'returnToDetail'=>$request->query('from') === 'show',
            'suppliers'=>DB::table('customer_supplier')->where('jenis_customer',2)->orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request, ?int $id = null)
    {
        $existing = $id ? DB::table('purchase_order_master')->where('purchase_order_id',$id)->first() : null;
        if ($id) {
            abort_unless($existing,404);
            $request->merge(['quotation_id'=>$existing->quotation_id,'quotation_detail_id'=>$existing->quotation_detail_id]);
        }
        $data = $request->validate([
            'quotation_id'=>['required','integer','exists:sebutharga_master,quotation_id'],
            'quotation_detail_id'=>['required','integer',Rule::exists('sebutharga_detail','quotation_detail_id')->where('quotation_id',$request->quotation_id)],
            'customer_id'=>['required','integer',Rule::exists('customer_supplier','customer_id')->where('jenis_customer',2)],
            'po_date'=>'required|date','items_json'=>'required|json',
            'discount_percent'=>'nullable|numeric|min:0|max:100',
            'sst_percent'=>'nullable|numeric|min:0|max:100',
            'delivery_address'=>'nullable|string','attention_supplier'=>'nullable|string|max:255',
            'attention_delivery'=>'nullable|string|max:255',
            'delivery_days'=>'required|integer|min:1|max:3650',
            'payment_days'=>'required|integer|min:1|max:3650',
            'additional_terms'=>'nullable|string|max:10000',
            'prepared_by'=>'nullable|string|max:255','approved_by'=>'nullable|string|max:255',
        ]);
        $items = json_decode($data['items_json'], true);
        $termsConditions = $this->termsConditions($data);
        if (! $existing) {
            abort_unless($this->finalQuotations()->where('detail.quotation_detail_id', $data['quotation_detail_id'])->exists(), 422, __('Sila pilih sebut harga Final yang dipersetujui.'));
        }
        validator(['items'=>$items], [
            'items'=>'required|array|min:1', 'items.*'=>'required|array',
            'items.*.description'=>'required|string','items.*.quantity'=>'required|integer|min:1|max:1000000',
            'items.*.unit'=>'required|string|max:50','items.*.price'=>'required|numeric|min:0|max:1000000',
        ])->validate();
        $gross = collect($items)->sum(fn($i)=>round((int)$i['quantity']*(float)$i['price'],2));
        $discount = round($gross * (float)($data['discount_percent'] ?? 0) / 100, 2);
        $sstRate = (float) ($data['sst_percent'] ?? 0);
        $sst = round(($gross - $discount) * $sstRate / 100, 2);
        $net = round($gross - $discount + $sst, 2);
        $request->merge(['discount_amount'=>$discount, 'sst_amount'=>$sst, 'net_amount'=>$net]);
        if ($request->input('_intent') === 'pdf') {
            $supplier = DB::table('customer_supplier')->where('customer_id',$data['customer_id'])->first();
            $order = (object) [
                'po_no'=>$existing?->po_no ?? $this->nextPoNumber($data['po_date']),
                'po_date'=>$data['po_date'], 'company_name'=>$supplier->company_name,
                'supplier_address'=>$supplier->address, 'supplier_phone'=>$supplier->phone_no,
                'attention_supplier'=>$request->attention_supplier, 'delivery_address'=>$request->delivery_address,
                'attention_delivery'=>$request->attention_delivery, 'gross_amount'=>$gross,
                'discount_percent'=>$data['discount_percent'] ?? 0, 'discount_amount'=>$discount,
                'sst_percent'=>$sstRate, 'sst_amount'=>$sst, 'net_amount'=>$net, 'terms_conditions'=>$termsConditions,
                'prepared_by'=>$request->prepared_by, 'approved_by'=>$request->approved_by,
            ];
            $document = $request->validate([
                'issuer_name'=>'nullable|string|max:255','issuer_address'=>'nullable|string|max:2000',
                'issuer_phone'=>'nullable|string|max:50','issuer_email'=>'nullable|email|max:255',
                'delivery_company'=>'nullable|string|max:255','delivery_phone'=>'nullable|string|max:50',
                'prepared_role'=>'nullable|string|max:255','approved_role'=>'nullable|string|max:255',
            ]);
            $items = collect($items)->map(fn ($item) => (object) [
                'item_description'=>$item['description'], 'quantity'=>$item['quantity'], 'unit'=>$item['unit'],
                'unit_price'=>$item['price'], 'subtotal'=>round($item['quantity']*$item['price'],2),
            ]);
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.purchase-order.pdf',compact('order','items','document'))
                ->setPaper('a4')->setOption('isRemoteEnabled',false)->download($order->po_no.'.pdf');
        }
        return DB::transaction(function () use ($request,$data,$items,$gross,$discount,$sstRate,$sst,$net,$existing,$id,$termsConditions) {
        DB::table('sebutharga_master')->where('quotation_id',$data['quotation_id'])->lockForUpdate()->first();
        $poNo = $existing ? $existing->po_no : $this->nextPoNumber($data['po_date']);
        $record = ['po_no'=>$poNo,'quotation_id'=>$data['quotation_id'],'quotation_detail_id'=>$data['quotation_detail_id'],'customer_id'=>$data['customer_id'],'po_date'=>$data['po_date'],'delivery_address'=>$request->delivery_address,'attention_supplier'=>$request->attention_supplier,'attention_delivery'=>$request->attention_delivery,'gross_amount'=>$gross,'discount_percent'=>$data['discount_percent'] ?? 0,'discount_amount'=>$discount,'sst_percent'=>$sstRate,'sst_amount'=>$sst,'net_amount'=>$net,'terms_conditions'=>$termsConditions,'prepared_by'=>$request->prepared_by,'approved_by'=>$request->approved_by,'updated_at'=>now()];
        if ($existing) {
            $record['updated_by']=$request->user()?->id;
            DB::table('purchase_order_master')->where('purchase_order_id',$id)->update($record);
            DB::table('purchase_order_item')->where('purchase_order_id',$id)->delete();
        } else {
            $id=DB::table('purchase_order_master')->insertGetId($record+['status_po'=>'Draf','created_at'=>now(),'created_by'=>$request->user()?->id]);
        }
        foreach($items as $item) DB::table('purchase_order_item')->insert(['purchase_order_id'=>$id,'item_description'=>$item['description']??'','quantity'=>$item['quantity']??0,'unit'=>$item['unit']??'','unit_price'=>$item['price']??0,'subtotal'=>($item['quantity']??0)*($item['price']??0),'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route($existing && $request->input('from') === 'show' ? 'purchase-order.show' : 'purchase-order.index', $existing && $request->input('from') === 'show' ? $id : [])
            ->with('success', "PO {$poNo} berjaya disimpan.");
        });
    }

    private function nextPoNumber(?string $date = null): string
    {
        $year = $date ? date('Y', strtotime($date)) : now()->format('Y');
        $prefix = "K1R-POO-{$year}-";
        $last = DB::table('purchase_order_master')->where('po_no', 'like', $prefix.'%')->orderByDesc('po_no')->value('po_no');
        $next = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function termsConditions(array $data): string
    {
        $terms = [
            '1. Sila nyatakan nombor Purchase Order (PO) ini di dalam Invois dan Nota Serahan (DO) rasmi anda.',
            '2. Penghantaran mestilah menepati spesifikasi teknikal yang dipesan. Barangan rosak/salah akan dipulangkan.',
            "3. Sila hantar bekalan dalam tempoh {$data['delivery_days']} hari bekerja dari tarikh dokumen ini dikeluarkan.",
            "4. Pembayaran penuh akan diproses dalam tempoh {$data['payment_days']} hari (Kredit) selepas pengesahan penerimaan DO & Invois.",
        ];

        $additional = trim((string) ($data['additional_terms'] ?? ''));
        $additional = preg_match('/^\d+\.\s*$/', $additional) ? '' : $additional;
        return implode("\n", $terms).($additional !== '' ? "\n".$additional : '');
    }
}
