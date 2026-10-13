<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembekal_quotation_master', function (Blueprint $table) {
            $table->date('quotation_date')->nullable()->after('quotation_no_supplier');
        });
    }

    public function down(): void
    {
        Schema::table('pembekal_quotation_master', function (Blueprint $table) {
            $table->dropColumn('quotation_date');
        });
    }
};
