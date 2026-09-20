<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupplierQuotationController extends Controller
{
    public function index()
    {
        $quotations = DB::table('pembekal_quotation_master as quotation')
            ->leftJoin('customer_supplier as supplier', 'supplier.customer_id', '=', 'quotation.supplier_id')
            ->orderByDesc('quotation.supplier_quotation_id')
            ->get(['quotation.*', 'supplier.company_name as supplier_registered_name']);

        return view('pages.sebut-harga-pembekal.index', compact('quotations'));
    }

    public function form(?int $id = null)
    {
        $quotation = $id ? DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail() : null;
        $items = $quotation ? DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->get() : collect();
        $suppliers = DB::table('customer_supplier')->where('jenis_customer', 2)->orderBy('company_name')->get(['customer_id', 'company_name', 'customer_name']);

        return view('pages.sebut-harga-pembekal.form', compact('quotation', 'items', 'suppliers'));
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'quotation_id' => ['nullable', 'integer', 'exists:sebutharga_master,quotation_id'],
            'supplier_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 2)],
            'quotation_no_supplier' => ['required', 'string', 'max:100'],
            'quotation_title' => ['nullable', 'string', 'max:255'],
            'person_in_charge' => ['nullable', 'string', 'max:255'],
            'supplier_quotation_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $supplier = DB::table('customer_supplier')->where('customer_id', $data['supplier_id'])->firstOrFail();
        $items = collect($data['items']);
        $filePath = null;
        $fileName = null;

        $file = $request->file('supplier_quotation_file');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $temporaryPath = $file->getPathname();
            if (! $file->isValid() || ! $temporaryPath || ! is_file($temporaryPath)) {
                return back()->withErrors(['supplier_quotation_file' => 'Fail PDF tidak berjaya diterima. Sila pilih fail PDF semula.'])->withInput();
            }

            $storedName = Str::uuid() . '.pdf';
            $filePath = 'pembekal-quotations/' . $storedName;
            Storage::disk('public')->put($filePath, file_get_contents($temporaryPath));
            $fileName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($data, $items, $supplier, $id, $filePath, $fileName) {
            $record = [
                'quotation_id' => $data['quotation_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'quotation_no_supplier' => $data['quotation_no_supplier'],
                'company_name' => $supplier->company_name,
                'quotation_title' => $data['quotation_title'] ?? null,
                'person_in_charge' => $data['person_in_charge'] ?? null,
                'updated_at' => now(),
            ];
            if ($filePath) {
                $record['file_path'] = $filePath;
                $record['file_name'] = $fileName;
            }

            if ($id) {
                DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->update($record);
                DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->delete();
                $supplierQuotationId = $id;
            } else {
                $record['created_at'] = now();
                $supplierQuotationId = DB::table('pembekal_quotation_master')->insertGetId($record);
            }

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];
                DB::table('pembekal_quotation_item')->insert([
                    'supplier_quotation_id' => $supplierQuotationId,
                    'item_description' => $item['description'],
                    'quantity' => $quantity,
                    'unit' => $item['unit'],
                    'unit_price' => $price,
                    'subtotal' => round($quantity * $price, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('sebut-harga-pembekal')->with('success', 'Sebut harga pembekal berjaya disimpan.');
    }

    public function delete(int $id)
    {
        $quotation = DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail();
        if ($quotation->file_path) Storage::disk('public')->delete($quotation->file_path);
        DB::table('pembekal_quotation_item')->where('supplier_quotation_id', $id)->delete();
        DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->delete();

        return redirect()->route('sebut-harga-pembekal');
    }

    public function download(int $id)
    {
        $quotation = DB::table('pembekal_quotation_master')->where('supplier_quotation_id', $id)->firstOrFail();
        abort_unless($quotation->file_path && Storage::disk('public')->exists($quotation->file_path), 404);

        return Storage::disk('public')->download($quotation->file_path, $quotation->file_name ?: 'sebut-harga-pembekal.pdf');
    }
}
