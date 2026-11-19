<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_voucher_invoice', function (Blueprint $table) {
            $table->renameColumn('supplier_document_id', 'invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_voucher_invoice', function (Blueprint $table) {
            $table->renameColumn('invoice_id', 'supplier_document_id');
        });
    }
};
