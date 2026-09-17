<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->unsignedInteger('final_no')->nullable()->after('draft_no');
        });

        DB::table('sebutharga_detail')
            ->whereRaw('LOWER(TRIM(status_draft)) = ?', ['final'])
            ->orderBy('quotation_id')
            ->orderBy('quotation_detail_id')
            ->get(['quotation_detail_id', 'quotation_id'])
            ->groupBy('quotation_id')
            ->each(function ($finals) {
                foreach ($finals->values() as $index => $final) {
                    DB::table('sebutharga_detail')
                        ->where('quotation_detail_id', $final->quotation_detail_id)
                        ->update(['final_no' => $index + 1]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn('final_no');
        });
    }
};
