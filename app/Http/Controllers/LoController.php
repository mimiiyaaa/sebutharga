<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
                'lo.quotation_id',
                'lo.quotation_detail_id',
                'customer.customer_name',
                'customer.company_name',
            ]);

        return view('pages.lo', compact('loDocuments'));
    }

    public function form(?int $id = null)
    {
        $lo = $id ? DB::table('lo_inden_master')->where('lo_inden_id', $id)->firstOrFail() : null;
        $finalQuotations = DB::table('sebutharga_detail as detail')
            ->join('sebutharga_master as quotation', 'quotation.quotation_id', '=', 'detail.quotation_id')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'detail.customer_id')
            ->leftJoin('lo_inden_master as linked_lo', 'linked_lo.quotation_detail_id', '=', 'detail.quotation_detail_id')
            ->whereRaw('LOWER(TRIM(detail.status_draft)) = ?', ['final'])
            ->whereNotNull('detail.sent_at')
            ->where(function ($query) use ($lo) {
                $query->whereNull('linked_lo.lo_inden_id');
                if ($lo?->quotation_detail_id) {
                    $query->orWhere('detail.quotation_detail_id', $lo->quotation_detail_id);
                }
            })
            ->where(function ($query) use ($lo) {
                $query->whereNull('detail.status_quotation')
                    ->orWhere('detail.status_quotation', 1);
                if ($lo?->quotation_detail_id) {
                    $query->orWhere('detail.quotation_detail_id', $lo->quotation_detail_id);
                }
            })
            ->orderBy('quotation.quotation_no')
            ->orderBy('detail.final_no')
            ->get([
                'detail.quotation_id', 'detail.quotation_detail_id', 'detail.customer_id',
                'detail.quotation_title', 'detail.jumlah_total', 'detail.final_no',
                'detail.status_quotation', 'quotation.quotation_no', 'customer.customer_name', 'customer.company_name',
                'linked_lo.lo_inden_no',
            ]);

        return view('pages.lo.form', compact('lo', 'finalQuotations'));
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'lo_inden_no' => ['required', 'string', 'max:100', Rule::unique('lo_inden_master', 'lo_inden_no')->ignore($id, 'lo_inden_id')],
            'quotation_detail_id' => ['required', 'integer'],
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

        $loId = DB::transaction(function () use ($data, $existing, $id, $documentPath, $request) {
            $currentLo = $id
                ? DB::table('lo_inden_master')->where('lo_inden_id', $id)->lockForUpdate()->firstOrFail()
                : null;
            $quotation = DB::table('sebutharga_detail')
                ->where('quotation_detail_id', $data['quotation_detail_id'])
                ->lockForUpdate()
                ->first();

            if (! $quotation || strtolower(trim((string) $quotation->status_draft)) !== 'final' || ! $quotation->sent_at || ! in_array($quotation->status_quotation, [null, 1], true)) {
                throw ValidationException::withMessages(['quotation_detail_id' => 'Sila pilih Sebut Harga Final yang menunggu LO atau berjaya tetapi belum menerima LO.']);
            }
            $alreadyLinked = DB::table('lo_inden_master')
                ->where('quotation_detail_id', $quotation->quotation_detail_id)
                ->when($id, fn ($query) => $query->where('lo_inden_id', '!=', $id))
                ->exists();
            if ($alreadyLinked) {
                throw ValidationException::withMessages(['quotation_detail_id' => 'Sebut harga ini telah mempunyai rekod LO.']);
            }

            $changedQuotation = $currentLo?->quotation_detail_id
                && (int) $currentLo->quotation_detail_id !== (int) $quotation->quotation_detail_id;
            if ($changedQuotation) {
                $previousStatus = $currentLo->quotation_status_before_lo;
                DB::table('sebutharga_detail')->where('quotation_detail_id', $currentLo->quotation_detail_id)
                    ->update(['status_quotation' => $previousStatus, 'updated_at' => now()]);
                DB::table('sebutharga_master')
                    ->where('quotation_id', $currentLo->quotation_id)
                    ->where('quotation_detail_id', $currentLo->quotation_detail_id)
                    ->update(['status_quotation' => $previousStatus, 'updated_at' => now()]);
            }

            $record = [
                'lo_inden_no' => trim($data['lo_inden_no']),
                'quotation_id' => $quotation->quotation_id,
                'quotation_detail_id' => $quotation->quotation_detail_id,
                'quotation_status_before_lo' => $changedQuotation || ! $currentLo
                    ? $quotation->status_quotation
                    : $currentLo->quotation_status_before_lo,
                'customer_id' => $quotation->customer_id,
                'lo_inden_date' => $data['lo_inden_date'],
                'amount' => $data['amount'],
                'document_file' => $documentPath,
                'updated_by' => $request->user()?->id,
                'updated_at' => now(),
            ];

            if ($currentLo) {
                DB::table('lo_inden_master')->where('lo_inden_id', $id)->update($record);
                $loId = $id;
            } else {
                $record['created_by'] = $request->user()?->id;
                $record['created_at'] = now();
                $loId = DB::table('lo_inden_master')->insertGetId($record);
            }

            DB::table('sebutharga_detail')->where('quotation_detail_id', $quotation->quotation_detail_id)
                ->update(['status_quotation' => 1, 'updated_at' => now()]);
            DB::table('sebutharga_master')->where('quotation_id', $quotation->quotation_id)
                ->update(['quotation_detail_id' => $quotation->quotation_detail_id, 'status_quotation' => 1, 'updated_at' => now()]);

            return $loId;
        });

        if ($id && $request->hasFile('document_file') && $existing?->document_file) {
            Storage::disk('public')->delete($existing->document_file);
        }

        if ($id) {
            return redirect()->route('lo.show', $id)->with('success', 'LO berjaya dikemaskini. Sebut harga kekal Berjaya.');
        }

        return redirect()->route('lo.show', $loId)->with('success', 'LO berjaya disimpan dan Sebut Harga Final telah ditandakan Berjaya.');
    }

    public function show(int $id)
    {
        $lo = DB::table('lo_inden_master as lo')
            ->leftJoin('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->leftJoin('sebutharga_master as quotation', 'quotation.quotation_id', '=', 'lo.quotation_id')
            ->leftJoin('sebutharga_detail as final_detail', 'final_detail.quotation_detail_id', '=', 'lo.quotation_detail_id')
            ->where('lo.lo_inden_id', $id)
            ->first([
                'lo.*',
                'customer.customer_name',
                'customer.company_name',
                'customer.address',
                'customer.phone_no',
                'customer.email',
                'quotation.quotation_no',
                'final_detail.final_no',
                'final_detail.quotation_title',
                'final_detail.quotation_date',
                'final_detail.status_quotation as quotation_status',
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
        DB::transaction(function () use ($lo, $id) {
            DB::table('lo_inden_master')->where('lo_inden_id', $id)->delete();
            if ($lo->quotation_detail_id) {
                $previousStatus = $lo->quotation_status_before_lo;
                DB::table('sebutharga_detail')->where('quotation_detail_id', $lo->quotation_detail_id)
                    ->update(['status_quotation' => $previousStatus, 'updated_at' => now()]);
                DB::table('sebutharga_master')->where('quotation_id', $lo->quotation_id)
                    ->where('quotation_detail_id', $lo->quotation_detail_id)
                    ->update(['status_quotation' => $previousStatus, 'updated_at' => now()]);
            }
        });
        if ($lo->document_file) Storage::disk('public')->delete($lo->document_file);

        return redirect()->route('lo')->with('success', 'LO berjaya dipadam.');
    }
}
