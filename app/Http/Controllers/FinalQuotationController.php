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
            ->whereColumn('d.quotation_detail_id', 'm.quotation_detail_id')
            ->select('d.*', 'm.quotation_no', 'c.company_name')
            ->orderBy('d.quotation_id')->get();
        $finals = $drafts->values();
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
                'draft_no' => ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->max('draft_no')) + 1,
                'final_no' => null,
                'quotation_date' => $source->quotation_date,
                'customer_id' => $source->customer_id,
                'quotation_title' => $source->quotation_title,
                'no_rujukan_pelanggan' => $source->no_rujukan_pelanggan,
                'status_quotation' => null,
                'draft_name' => null,
                'jumlah_total' => $source->jumlah_total,
                'terma_syarat' => $source->terma_syarat,
                'document_data' => $source->document_data,
                'disediakan_oleh' => $source->disediakan_oleh,
                'disediakan_role' => $source->disediakan_role ?? null,
                'diterima_oleh' => $source->diterima_oleh,
                'diterima_role' => $source->diterima_role ?? null,
                'status_draft' => 'Draf',
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

            return $newDraftId;
        });

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $newDraftId, 'edit' => 1])
            ->with('success', 'Draf baharu berjaya dibuat untuk diedit.');
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

    public function send(int $id, int $draftId)
    {
        $this->updateDecisionState($id, $draftId, ['sent_at' => now(), 'status_quotation' => null], null);

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $draftId, 'from' => 'final'])
            ->with('success', 'Sebut harga telah dihantar dan menunggu keputusan pelanggan.');
    }

    public function decide(int $id, int $draftId, int $decision)
    {
        $this->updateDecisionState($id, $draftId, ['status_quotation' => $decision], $decision);

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $draftId, 'from' => 'final'])
            ->with('success', $decision === 1 ? 'Sebut harga ditandakan sebagai setuju.' : 'Sebut harga ditandakan sebagai tidak setuju.');
    }

    public function undoDecision(int $id, int $draftId)
    {
        $this->updateDecisionState($id, $draftId, ['status_quotation' => null], null);

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $draftId, 'from' => 'final'])
            ->with('success', 'Keputusan telah dibatalkan. Sebut harga kembali menunggu keputusan.');
    }

    public function undoSend(int $id, int $draftId)
    {
        $this->updateDecisionState($id, $draftId, ['sent_at' => null, 'status_quotation' => null], null);

        return redirect()->route('sebut-harga.edit', [$id, 'draft' => $draftId, 'from' => 'final'])
            ->with('success', 'Status penghantaran telah dibatalkan.');
    }

    private function updateDecisionState(int $id, int $draftId, array $detailState, ?int $masterDecision): void
    {
        DB::transaction(function () use ($id, $draftId, $detailState, $masterDecision) {
            $draft = DB::table('sebutharga_detail')
                ->where('quotation_id', $id)
                ->where('quotation_detail_id', $draftId)
                ->lockForUpdate()
                ->first();

            abort_unless($draft && strtolower(trim((string) $draft->status_draft)) === 'final', 404);

            DB::table('sebutharga_detail')
                ->where('quotation_detail_id', $draftId)
                ->update($detailState + ['updated_at' => now()]);

            DB::table('sebutharga_master')
                ->where('quotation_id', $id)
                ->update([
                    'quotation_detail_id' => $draftId,
                    'status_quotation' => $masterDecision,
                    'updated_at' => now(),
                ]);
        });
    }
}
