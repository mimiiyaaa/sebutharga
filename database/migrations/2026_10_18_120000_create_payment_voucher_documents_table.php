<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_voucher_documents', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 100);
            $table->date('voucher_date');
            $table->string('payee_name', 255);
            $table->text('description')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_mime', 100);
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->unique('voucher_no');
            $table->index('voucher_date');
            $table->index('payee_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_documents');
    }
};
