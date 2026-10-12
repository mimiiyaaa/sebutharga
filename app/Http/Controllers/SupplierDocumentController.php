<?php

namespace App\Http\Controllers;

use App\Services\SupplierDocumentParser;
use App\Services\SupplierQuotationOcr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupplierDocumentController extends Controller
{
    private function context(Request $request): array
    {
        $invoice = $request->routeIs('invoice.*');
        return ['kind' => $invoice ? 'invoice' : 'do', 'prefix' => $invoice ? 'invoice.supplier' : 'delivery-order.supplier',
            'title' => $invoice ? __('Invois Daripada Pembekal') : __('DO Daripada Pembekal'), 'isInvoice' => $invoice];
    }

    public function index(Request $request)
    {
        $context = $this->context($request);
        $documents = DB::table('supplier_documents')->where('kind', $context['kind'])->orderByDesc('id')->get();
        return view('pages.supplier-documents.index', $context + compact('documents'));
    }

    public function create(Request $request)
    {
        return view('pages.supplier-documents.form', $this->context($request) + [
            'suppliers' => DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')->get(['customer_id','company_name']),
        ]);
    }

    private function extractionKey(Request $request, $file): string
    {
        return 'supplier-document:'.$request->user()->id.':'.hash_file('sha256', $file->getPathname());
    }

    public function extract(Request $request, SupplierQuotationOcr $ocr, SupplierDocumentParser $parser)
    {
        $request->validate(['document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $file = $request->file('document_file');
        try { $result = $ocr->extractText($file); }
        catch (\RuntimeException $e) { return response()->json(['message' => $e->getMessage()], 422); }
        $data = $parser->parse(mb_substr($result['text'], 0, 150000), $this->context($request)['kind']);
        $data['supplier_id'] = $data['supplier_name'] ? DB::table('customer_supplier')->where('jenis_customer', 2)
            ->whereRaw('UPPER(TRIM(company_name)) = ?', [mb_strtoupper(trim($data['supplier_name']))])->value('customer_id') : null;
        Cache::put($this->extractionKey($request, $file), $result['method'], now()->addHour());
        return response()->json(['data' => $data, 'method' => $result['method'], 'text' => mb_substr($result['text'], 0, 50000),
            'message' => __('Bacaan selesai. Sila semak maklumat dan item dengan dokumen asal sebelum simpan.')]);
    }

    public function store(Request $request)
    {
        $context = $this->context($request);
        $data = $request->validate([
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'supplier_id' => ['required','integer',Rule::exists('customer_supplier','customer_id')->where('jenis_customer',2)],
            'document_no' => ['required','string','max:100', Rule::unique('supplier_documents')->where('kind',$context['kind'])->where('supplier_id',$request->input('supplier_id'))],
            'document_date' => 'required|date_format:Y-m-d', 'po_reference' => 'nullable|string|max:100',
            'source' => ['required',Rule::in(['PDF','E-mel','WhatsApp','Surat','Serahan Tangan','Lain-lain'])],
            'source_details' => 'nullable|string|max:1000','notes' => 'nullable|string|max:3000',
            'items' => 'required|array|min:1|max:100','items.*' => 'required|array',
            'items.*.description' => 'required|string|max:2000', 'items.*.quantity' => 'required|numeric|min:0.01|max:1000000|decimal:0,2',
            'items.*.unit' => 'required|string|max:50','items.*.price' => $context['isInvoice'] ? 'required|numeric|min:0|max:1000000|decimal:0,2' : 'nullable|numeric|min:0|max:1000000',
            'total_amount' => $context['isInvoice'] ? 'required|numeric|min:0|max:9999999999.99|decimal:0,2' : 'nullable',
            'reviewed' => 'accepted',
        ]);
        $items = collect($data['items'])->map(fn ($item) => ['description'=>$item['description'],'quantity'=>(float)$item['quantity'],'unit'=>$item['unit'],'price'=>$context['isInvoice'] ? (float)$item['price'] : 0])->values()->all();
        $file = $request->file('document_file');
        $path = $file->store('supplier-documents/'.$context['kind'], 'local');
        if (!$path) throw ValidationException::withMessages(['document_file'=>__('Fail tidak dapat disimpan. Sila cuba semula.')]);
        try {
            DB::table('supplier_documents')->insert([
                'kind'=>$context['kind'],'supplier_id'=>$data['supplier_id'],
                'supplier_name'=>DB::table('customer_supplier')->where('customer_id',$data['supplier_id'])->value('company_name'),
                'document_no'=>$data['document_no'],'document_date'=>$data['document_date'],'po_reference'=>$data['po_reference'] ?? null,
                'source'=>$data['source'],'source_details'=>$data['source_details'] ?? null,
                'file_path'=>$path,'file_name'=>mb_substr(basename($file->getClientOriginalName()),0,255),'file_mime'=>$file->getMimeType(),
                'extraction_method'=>Cache::get($this->extractionKey($request,$file),__('Semakan manual')),
                'items'=>json_encode($items, JSON_UNESCAPED_UNICODE),'total_amount'=>$context['isInvoice'] ? $data['total_amount'] : null,
                'notes'=>$data['notes'] ?? null,'created_by'=>$request->user()->id,'created_at'=>now(),'updated_at'=>now(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) throw ValidationException::withMessages(['document_no'=>__('Nombor dokumen pembekal ini sudah disimpan.')]);
            throw $e;
        }
        return redirect()->route($context['prefix'])->with('success',__('Dokumen pembekal berjaya disimpan.'));
    }

    private function document(Request $request, int $id)
    {
        return DB::table('supplier_documents')->where('kind',$this->context($request)['kind'])->where('id',$id)->firstOrFail();
    }

    public function show(Request $request, int $id)
    {
        $document = $this->document($request,$id);
        return view('pages.supplier-documents.show', $this->context($request) + ['document'=>$document,'items'=>json_decode($document->items,true)]);
    }

    public function file(Request $request, int $id)
    {
        $document = $this->document($request,$id);
        abort_unless(Storage::disk('local')->exists($document->file_path),404);
        $headers = ['Content-Type'=>$document->file_mime,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store'];
        return $request->boolean('download') ? Storage::disk('local')->download($document->file_path,$document->file_name,$headers)
            : Storage::disk('local')->response($document->file_path,$document->file_name,$headers,'inline');
    }

    public function destroy(Request $request, int $id)
    {
        $document = $this->document($request,$id);
        $disk = Storage::disk('local');
        if ($disk->exists($document->file_path) && !$disk->delete($document->file_path)) return back()->with('error',__('Fail tidak dapat dipadam. Sila cuba semula.'));
        DB::table('supplier_documents')->where('id',$document->id)->where('kind',$document->kind)->delete();
        return redirect()->route($this->context($request)['prefix'])->with('success',__('Dokumen pembekal berjaya dipadam.'));
    }
}
