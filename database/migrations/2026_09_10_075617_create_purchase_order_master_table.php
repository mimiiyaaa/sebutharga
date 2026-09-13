<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_master', function (Blueprint $table) {
            $table->increments('purchase_order_id');

            $table->string('po_no', 50)->unique();

            $table->unsignedInteger('quotation_detail_id');
            $table->foreignId('customer_id');
            $table->unsignedInteger('lo_inden_id')->nullable();

            $table->date('po_date');

            $table->text('delivery_address')->nullable();

            $table->string('attention_supplier', 255)->nullable();
            $table->string('attention_delivery', 255)->nullable();

            $table->decimal('gross_amount', 12, 2)->default(0.00);
            $table->decimal('discount_percent', 5, 2)->nullable()->default(0.00);
            $table->decimal('discount_amount', 12, 2)->nullable()->default(0.00);
            $table->decimal('net_amount', 12, 2)->default(0.00);

            $table->text('terms_conditions')->nullable();

            $table->string('prepared_by', 255)->nullable();
            $table->string('approved_by', 255)->nullable();

            $table->string('status_po', 30)->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->foreign('quotation_detail_id')
                ->references('quotation_detail_id')
                ->on('sebutharga_detail');

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customer_supplier');

            $table->foreign('lo_inden_id')
                ->references('lo_inden_id')
                ->on('lo_inden_master');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_master');
    }
};