<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_master', function (Blueprint $table) {

            // Primary Key
            $table->increments('quotation_id');

            // Quotation Information
            $table->string('quotation_no', 50)->unique();

            // Reference to customer_supplier
            $table->unsignedInteger('customer_id');

            // Reference to quotation detail
            $table->unsignedInteger('quotation_detail_id')->nullable();

            $table->date('quotation_date');

            $table->string('quotation_title', 255)->nullable();

            $table->string('no_rujukan_pelanggan', 100)->nullable();

            // 1 = Berjaya / Setuju
            // 0 = Gagal / Tidak Setuju
            $table->integer('status_quotation')->nullable();

            // Audit Log
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            // Date & Time
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_master');
    }
};