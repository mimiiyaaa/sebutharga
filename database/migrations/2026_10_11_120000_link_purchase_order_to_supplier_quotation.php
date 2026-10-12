<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->unsignedInteger('supplier_quotation_id')->nullable()->after('quotation_detail_id');
            $table->unsignedInteger('quotation_id')->nullable()->change();
            $table->unsignedInteger('quotation_detail_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->dropColumn('supplier_quotation_id');
            $table->unsignedInteger('quotation_id')->nullable(false)->change();
            $table->unsignedInteger('quotation_detail_id')->nullable(false)->change();
        });
    }
};
