<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $documents = DB::table('payment_voucher_documents')->orderByDesc('id')->get();

        return view('pages.baucar-bayaran.index', [
            'title' => __('Baucar Bayaran'),
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return view('pages.baucar-bayaran.form', ['title' => __('Tambah Baucar Bayaran')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'voucher_no' => ['required', 'string', 'max:100', Rule::unique('payment_voucher_documents', 'voucher_no')],
            'voucher_date' => ['required', 'date_format:Y-m-d'],
            'payee_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', Rule::in(['Tunai', 'Pindahan Bank', 'Cek', 'Lain-lain'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('document_file');
        $path = $file->store('payment-vouchers', 'local');

        DB::table('payment_voucher_documents')->insert([
            'voucher_no' => $data['voucher_no'],
            'voucher_date' => $data['voucher_date'],
            'payee_name' => $data['payee_name'],
            'payment_method' => $data['payment_method'],
            'description' => $data['description'],
            'total_amount' => $data['total_amount'],
            'file_path' => $path,
            'file_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
            'file_mime' => $file->getMimeType(),
            'uploaded_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya disimpan.'));
    }

    public function show(int $id)
    {
        $document = $this->document($id);

        return view('pages.baucar-bayaran.show', [
            'title' => __('Lihat Baucar Bayaran'),
            'document' => $document,
        ]);
    }

    public function file(Request $request, int $id)
    {
        $document = $this->document($id);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $headers = [
            'Content-Type' => $document->file_mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        return $request->boolean('download')
            ? Storage::disk('local')->download($document->file_path, $document->file_name, $headers)
            : Storage::disk('local')->response($document->file_path, $document->file_name, $headers, 'inline');
    }

    public function destroy(int $id)
    {
        $document = $this->document($id);
        $disk = Storage::disk('local');

        if ($disk->exists($document->file_path)) {
            $disk->delete($document->file_path);
        }

        DB::table('payment_voucher_documents')->where('id', $document->id)->delete();

        return redirect()->route('baucar-bayaran.index')->with('success', __('Baucar bayaran berjaya dipadam.'));
    }

    private function document(int $id): object
    {
        return DB::table('payment_voucher_documents')->where('id', $id)->firstOrFail();
    }
}
