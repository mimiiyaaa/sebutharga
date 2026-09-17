<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FinalQuotationController extends Controller
{
    public function index()
    {
        $drafts = DB::table('sebutharga_detail as d')
            ->join('sebutharga_master as m', 'm.quotation_id', '=', 'd.quotation_id')
            ->leftJoin('customer_supplier as c', 'c.customer_id', '=', 'm.customer_id')
            ->whereRaw('LOWER(TRIM(d.status_draft)) = ?', ['final'])
            ->select('d.*', 'm.quotation_no', 'c.company_name')
            ->orderByDesc('d.updated_at')->paginate(15);
        return view('pages.sebut-harga.final', compact('drafts'));
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
