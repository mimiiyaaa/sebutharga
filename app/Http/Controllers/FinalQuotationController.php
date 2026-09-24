<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinalQuotationController extends Controller
{
    public function index()
    {
        $drafts = DB::table('sebutharga_detail as d')
            ->join('sebutharga_master as m', 'm.quotation_id', '=', 'd.quotation_id')
            ->leftJoin('customer_supplier as c', 'c.customer_id', '=', 'm.customer_id')
            ->whereRaw('LOWER(TRIM(d.status_draft)) = ?', ['final'])
            ->select('d.*', 'm.quotation_no', 'm.quotation_detail_id as active_detail_id', 'c.company_name')
            ->orderBy('d.quotation_id')
            ->orderBy('d.final_no')
            ->get();

        $finals = $drafts
            ->groupBy('quotation_id')
            ->map(function ($versions) {
                $selected = $versions->firstWhere('quotation_detail_id', $versions->first()->active_detail_id)
                    ?: $versions->last();
                $selected->versions = $versions->map(fn ($version) => [
                    'id' => $version->quotation_detail_id,
                    'label' => 'Versi ' . ($version->final_no ?? $version->draft_no),
                    'viewUrl' => route('sebut-harga.edit', [$version->quotation_id, 'draft' => $version->quotation_detail_id, 'from' => 'final']),
                ])->values();

                return $selected;
            })
            ->values();
        return view('pages.sebut-harga.final', compact('finals'));
    }

    public function newVersion(Request $request, int $id, int $draftId)
    {
        $newVersionId = DB::transaction(function () use ($request, $id, $draftId) {
            $master = DB::table('sebutharga_master')->where('quotation_id', $id)->lockForUpdate()->first();
            abort_unless($master, 404);
            $source = DB::table('sebutharga_detail')->where('quotation_id', $id)->where('quotation_detail_id', $draftId)->lockForUpdate()->first();
            abort_unless($source && strtolower(trim($source->status_draft)) === 'final', 404);

            $userId = $request->user()?->id;
            $newVersionId = DB::table('sebutharga_detail')->insertGetId([
                'quotation_id' => $id,
                'draft_no' => ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->max('draft_no')) + 1,
                'final_no' => ((int) DB::table('sebutharga_detail')->where('quotation_id', $id)->whereRaw('LOWER(TRIM(status_draft)) = ?', ['final'])->max('final_no')) + 1,
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
                'disediakan_role' => property_exists($source, 'disediakan_role') ? $source->disediakan_role : null,
                'diterima_oleh' => $source->diterima_oleh,
                'diterima_role' => property_exists($source, 'diterima_role') ? $source->diterima_role : null,
                'status_draft' => 'Final',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = DB::table('sebutharga_item')->where('quotation_detail_id', $draftId)->get();
            foreach ($items as $item) {
                DB::table('sebutharga_item')->insert([
                    'quotation_detail_id' => $newVersionId,
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

            DB::table('sebutharga_master')
                ->where('quotation_id', $id)
                ->update([
                    'quotation_detail_id' => $newVersionId,
                    'status_quotation' => null,
                    'updated_at' => now(),
                ]);

            return $newVersionId;
        });

        return redirect()->route('sebut-harga.final')
            ->with('success', 'Versi baharu berjaya dicipta daripada sebut harga Final.');
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

    public function send(Request $request, int $id, int $draftId)
    {
        $data = $request->validate([
            'sent_by' => ['required', 'string', 'max:255'],
            'sent_date' => ['required', 'date_format:m/d/Y'],
            'sent_time' => ['required', 'date_format:h:i A'],
        ]);
        $sentAt = Carbon::createFromFormat('m/d/Y h:i A', $data['sent_date'].' '.$data['sent_time']);

        $this->updateDecisionState($id, $draftId, ['sent_at' => $sentAt, 'sent_by' => $data['sent_by'], 'status_quotation' => null], null);

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
        $this->updateDecisionState($id, $draftId, ['sent_at' => null, 'sent_by' => null, 'status_quotation' => null], null);

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
