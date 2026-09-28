<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lo_inden_master', function (Blueprint $table) {
            $table->unsignedInteger('quotation_id')->nullable()->after('lo_inden_no');
            $table->unsignedInteger('quotation_detail_id')->nullable()->unique()->after('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::table('lo_inden_master', function (Blueprint $table) {
            $table->dropUnique(['quotation_detail_id']);
            $table->dropColumn(['quotation_id', 'quotation_detail_id']);
        });
    }
};
