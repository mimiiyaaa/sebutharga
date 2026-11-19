<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->unsignedInteger('supplier_quotation_id')->nullable()->after('supplier_id');
            $table->index('supplier_quotation_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->dropIndex(['supplier_quotation_id']);
            $table->dropColumn('supplier_quotation_id');
        });
    }
};
