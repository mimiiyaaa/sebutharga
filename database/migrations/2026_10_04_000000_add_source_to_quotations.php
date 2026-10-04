<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->string('sumber_sebut_harga', 50)->nullable()->after('draft_name');
        });

        Schema::table('pembekal_quotation_master', function (Blueprint $table) {
            $table->string('sumber_sebut_harga', 50)->nullable()->after('quotation_title');
        });
    }

    public function down(): void
    {
        Schema::table('pembekal_quotation_master', function (Blueprint $table) {
            $table->dropColumn('sumber_sebut_harga');
        });

        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn('sumber_sebut_harga');
        });
    }
};
