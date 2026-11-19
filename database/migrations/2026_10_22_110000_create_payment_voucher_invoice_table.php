<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_voucher_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_voucher_id');
            $table->unsignedBigInteger('supplier_document_id');
            $table->timestamps();

            $table->unique(['payment_voucher_id', 'supplier_document_id'], 'pvi_voucher_invoice_unique');
            $table->index('supplier_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_invoice');
    }
};
