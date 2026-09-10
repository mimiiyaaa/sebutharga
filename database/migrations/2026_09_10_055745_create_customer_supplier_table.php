<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_supplier', function (Blueprint $table) {

            // Primary Key
            $table->id('customer_id');

            // Customer / Supplier Information
            $table->string('customer_code', 50)->unique();

            // 1 = Customer, 2 = Supplier
            $table->integer('jenis_customer');

            $table->string('customer_name', 255)->nullable();
            $table->string('company_name', 255);

            $table->text('address');
            $table->string('phone_no', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('reference_no', 100)->nullable();

            // Audit Log - User ID only, no Foreign Key constraint
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // created_at & updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_supplier');
    }
};