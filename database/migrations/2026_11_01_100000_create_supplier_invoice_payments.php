<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained('payment_voucher_documents')->cascadeOnDelete();
            $table->foreignId('supplier_document_id')->constrained('supplier_documents')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->unique(['payment_voucher_id', 'supplier_document_id'], 'supplier_invoice_payment_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('supplier_invoice_payments'); }
};
