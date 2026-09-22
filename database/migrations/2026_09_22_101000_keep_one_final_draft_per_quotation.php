<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sebutharga_master')->orderBy('quotation_id')->each(function ($master) {
            $finals = DB::table('sebutharga_detail')
                ->where('quotation_id', $master->quotation_id)
                ->whereRaw('LOWER(TRIM(status_draft)) = ?', ['final'])
                ->orderByDesc('updated_at')
                ->orderByDesc('quotation_detail_id')
                ->get();

            if ($finals->count() < 2) {
                return;
            }

            $kept = $finals->firstWhere('quotation_detail_id', $master->quotation_detail_id) ?: $finals->first();

            DB::table('sebutharga_detail')
                ->where('quotation_id', $master->quotation_id)
                ->whereRaw('LOWER(TRIM(status_draft)) = ?', ['final'])
                ->where('quotation_detail_id', '!=', $kept->quotation_detail_id)
                ->update([
                    'status_draft' => 'Draf',
                    'final_no' => null,
                    'sent_at' => null,
                    'status_quotation' => null,
                    'updated_at' => now(),
                ]);

            DB::table('sebutharga_master')->where('quotation_id', $master->quotation_id)->update([
                'quotation_detail_id' => $kept->quotation_detail_id,
                'status_quotation' => $kept->status_quotation,
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
    }
};
