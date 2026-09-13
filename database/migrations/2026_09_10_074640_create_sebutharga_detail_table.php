<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_detail', function (Blueprint $table) {

            // Primary Key
            $table->increments('quotation_detail_id');

            // Reference to sebutharga_master
            // No Foreign Key Constraint
            $table->unsignedInteger('quotation_id');

            // Quotation Version / Draft
            $table->integer('draft_no')->default(1);

            // Total Amount
            $table->decimal('jumlah_total', 12, 2)->default(0.00);

            // Terms & Conditions
            $table->text('terma_syarat')->nullable();

            // Prepared / Accepted By
            $table->string('disediakan_oleh', 255)->nullable();
            $table->string('diterima_oleh', 255)->nullable();

            // Example: Draft / Final
            $table->string('status_draft', 20)->nullable();

            // Audit Log
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            // created_at & updated_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_detail');
    }
};