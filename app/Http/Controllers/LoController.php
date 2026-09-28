<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LoController extends Controller
{
    public function index()
    {
        $loDocuments = DB::table('lo_inden_master as lo')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->orderByDesc('lo.lo_inden_date')
            ->orderByDesc('lo.lo_inden_id')
            ->get([
                'lo.lo_inden_id',
                'lo.lo_inden_no',
                'lo.lo_inden_date',
                'lo.amount',
                'lo.document_file',
                'customer.customer_name',
                'customer.company_name',
            ]);

        return view('pages.lo', compact('loDocuments'));
    }

    public function form(?int $id = null)
    {
        $lo = $id ? DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail() : null;
        $customers = DB::table('customer_supplier')
            ->where('jenis_customer', 1)
            ->orderBy('company_name')
            ->get(['customer_id', 'customer_name', 'company_name']);

        return view('pages.lo.form', compact('lo', 'customers'));
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'lo_inden_no' => ['required', 'string', 'max:100', Rule::unique('lo_inden_master', 'lo_inden_no')->ignore($id, 'lo_inden_id')],
            'customer_id' => ['required', 'integer', Rule::exists('customer_supplier', 'customer_id')->where('jenis_customer', 1)],
            'lo_inden_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'document_file' => [$id ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $existing = $id ? DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail() : null;
        $documentPath = $existing?->document_file;

        if ($file = $request->file('document_file')) {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $documentPath = $file->storeAs('lo-documents', Str::uuid() . '.' . $extension, 'public');
        }

        $record = [
            'lo_inden_no' => trim($data['lo_inden_no']),
            'customer_id' => $data['customer_id'],
            'lo_inden_date' => $data['lo_inden_date'],
            'amount' => $data['amount'],
            'document_file' => $documentPath,
            'updated_by' => $request->user()?->id,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('lo_inden_master')->where('lo_inden_id', $id)->update($record);
            if ($request->hasFile('document_file') && $existing?->document_file) {
                Storage::disk('public')->delete($existing->document_file);
            }

            return redirect()->route('lo.show', $id)->with('success', 'LO berjaya dikemaskini.');
        }

        $record['created_by'] = $request->user()?->id;
        $record['created_at'] = now();
        $loId = DB::table('lo_inden_master')->insertGetId($record);

        return redirect()->route('lo.show', $loId)->with('success', 'LO berjaya disimpan.');
    }

    public function show(int $id)
    {
        $lo = DB::table('lo_inden_master as lo')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->where('lo.lo_inden_id', $id)
            ->first([
                'lo.*',
                'customer.customer_name',
                'customer.company_name',
                'customer.address',
                'customer.phone_no',
                'customer.email',
            ]);
        abort_unless($lo, 404);

        return view('pages.lo.show', compact('lo'));
    }

    public function document(int $id)
    {
        $lo = DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail();
        abort_unless($lo->document_file && Storage::disk('public')->exists($lo->document_file), 404);

        return response()->file(Storage::disk('public')->path($lo->document_file));
    }

    public function delete(int $id)
    {
        $lo = DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail();
        if ($lo->document_file) {
            Storage::disk('public')->delete($lo->document_file);
        }
        DB::table('lo_inden_master')->where('lo_inden_id', $id)->delete();

        return redirect()->route('lo')->with('success', 'LO berjaya dipadam.');
    }
}
