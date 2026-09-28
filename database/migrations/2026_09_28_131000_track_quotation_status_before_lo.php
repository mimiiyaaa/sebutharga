<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lo_inden_master', function (Blueprint $table) {
            $table->tinyInteger('quotation_status_before_lo')->nullable()->after('quotation_detail_id');
        });
    }

    public function down(): void
    {
        Schema::table('lo_inden_master', function (Blueprint $table) {
            $table->dropColumn('quotation_status_before_lo');
        });
    }
};
