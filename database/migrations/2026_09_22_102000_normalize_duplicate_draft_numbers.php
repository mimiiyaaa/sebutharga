<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sebutharga_master')->orderBy('quotation_id')->each(function ($master) {
            $drafts = DB::table('sebutharga_detail')
                ->where('quotation_id', $master->quotation_id)
                ->orderBy('draft_no')
                ->orderBy('created_at')
                ->orderBy('quotation_detail_id')
                ->get();

            if ($drafts->isEmpty()) {
                return;
            }

            $selectedId = (int) $master->quotation_detail_id;
            $selected = $drafts->firstWhere('quotation_detail_id', $selectedId);
            $reservedNo = $selected && (int) $selected->draft_no > 0
                ? (int) $selected->draft_no
                : null;
            $nextNo = 1;

            foreach ($drafts as $draft) {
                if ((int) $draft->quotation_detail_id === $selectedId && $reservedNo !== null) {
                    $draftNo = $reservedNo;
                } else {
                    while ($reservedNo !== null && $nextNo === $reservedNo) {
                        $nextNo++;
                    }
                    $draftNo = $nextNo++;
                }

                DB::table('sebutharga_detail')
                    ->where('quotation_detail_id', $draft->quotation_detail_id)
                    ->update(['draft_no' => $draftNo]);
            }
        });
    }

    public function down(): void
    {
        // Draft numbers are data labels; the previous duplicate values cannot
        // be restored safely without a snapshot of the original database.
    }
};
