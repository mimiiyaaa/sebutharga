<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->string('voucher_type', 20)->default('customer')->after('voucher_no');
            $table->unsignedInteger('supplier_id')->nullable()->after('payee_name');
            $table->index(['voucher_type', 'voucher_date']);
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->dropIndex(['voucher_type', 'voucher_date']);
            $table->dropIndex(['supplier_id']);
            $table->dropColumn(['voucher_type', 'supplier_id']);
        });
    }
};
