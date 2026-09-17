<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FinalQuotationController extends Controller
{
    public function index()
    {
        $drafts = DB::table('sebutharga_detail as d')
            ->join('sebutharga_master as m', 'm.quotation_id', '=', 'd.quotation_id')
            ->leftJoin('customer_supplier as c', 'c.customer_id', '=', 'm.customer_id')
            ->whereRaw('LOWER(TRIM(d.status_draft)) = ?', ['final'])
            ->select('d.*', 'm.quotation_no', 'c.company_name')
            ->orderBy('d.quotation_id')->orderBy('d.final_no')->get();
        $finals = $drafts->groupBy('quotation_id')->map(function ($versions) {
            $selected = $versions->sortByDesc('final_no')->first();
            $selected->final_versions = $versions->values();
            return $selected;
        })->values();
        return view('pages.sebut-harga.final', compact('finals'));
    }

    public function newVersion(Request $request, int $id, int $draftId)
    {
        $newDraftId = DB::transaction(function () use ($request, $id, $draftId) {
            $master = DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
            abort_unless($master, 404);
            $source = DB::table('sebutharga_detail')->where('quotation_id', $id)->where('quotation_detail_id', $draftId)->lockForUpdate()->first();
            abort_unless($source && strtolower(trim($source->status_draft)) === 'final', 404);

            $userId = $request->user()?->id;
            $newDraftId = DB::table('sebutharga_detail')->insertGetId([
                'quotation_id' => $id,
                'draft_no' => $source->draft_no,
                'final_no' => ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->whereRaw('LOWER(TRIM(status_draft)) = ?', ['final'])->max('final_no')) + 1,
                'quotation_date' => $source->quotation_date,
                'customer_id' => $source->customer_id,
                'quotation_title' => $source->quotation_title,
                'no_rujukan_pelanggan' => $source->no_rujukan_pelanggan,
                'status_quotation' => $source->status_quotation,
                'draft_name' => null,
                'jumlah_total' => $source->jumlah_total,
                'terma_syarat' => $source->terma_syarat,
                'document_data' => $source->document_data,
                'disediakan_oleh' => $source->disediakan_oleh,
                'disediakan_role' => $source->disediakan_role,
                'diterima_oleh' => $source->diterima_oleh,
                'diterima_role' => $source->diterima_role,
                'status_draft' => 'Final',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = DB::table('sebutharga_item')->where('quotation_detail_id', $draftId)->get();
            foreach ($items as $item) {
                DB::table('sebutharga_item')->insert([
                    'quotation_detail_id' => $newDraftId,
                    'item_description' => $item->item_description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('sebutharga_master')->where('quotation_id', $id)->update([
                'quotation_detail_id' => $newDraftId,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

            return $newDraftId;
        });

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $newDraftId, 'from' => 'final', 'edit' => 1])
            ->with('success', 'Versi Final baharu berjaya dibuat untuk diedit.');
    }

    public function undo(int $id, int $draftId)
    {
        DB::transaction(function () use ($id, $draftId) {
            $master = DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
            abort_unless($master, 404);
            $draft = DB::table('sebutharga_detail')->where('quotation_id', $id)->where('quotation_detail_id', $draftId)->lockForUpdate()->first();
            abort_unless($draft, 404);
            DB::table('sebutharga_detail')->where('quotation_detail_id', $draftId)->update(['status_draft'=>'Draf', 'updated_at'=>now()]);
        });
        return redirect()->route('sebut-harga.edit', [$id, 'draft'=>$draftId]);
    }
}
