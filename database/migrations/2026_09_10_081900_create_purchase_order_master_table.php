<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_master', function (Blueprint $table) {

            // Primary Key
            $table->increments('purchase_order_id');

            // PO Information
            $table->string('po_no', 50)->unique();

            // Reference to sebutharga_master
            // No Foreign Key Constraint
            $table->unsignedInteger('quotation_id');

            // Reference to sebutharga_detail
            // No Foreign Key Constraint
            $table->unsignedInteger('quotation_detail_id');

            // Supplier ID from customer_supplier
            // jenis_customer = 2
            $table->unsignedInteger('customer_id');

            // Reference to LO / Indent
            $table->unsignedInteger('lo_inden_id')->nullable();

            // PO Date
            $table->date('po_date');

            // Delivery Information
            $table->text('delivery_address')->nullable();
            $table->string('attention_supplier', 255)->nullable();
            $table->string('attention_delivery', 255)->nullable();

            // Amount
            $table->decimal('gross_amount', 12, 2)->default(0.00);

            $table->decimal('discount_percent', 5, 2)
                ->nullable()
                ->default(0.00);

            $table->decimal('discount_amount', 12, 2)
                ->nullable()
                ->default(0.00);

            $table->decimal('net_amount', 12, 2)->default(0.00);

            // Terms & Conditions
            $table->text('terms_conditions')->nullable();

            // Prepared / Approved By
            $table->string('prepared_by', 255)->nullable();
            $table->string('approved_by', 255)->nullable();

            // PO Status
            $table->string('status_po', 30)->nullable();

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
        Schema::dropIfExists('purchase_order_master');
    }
};