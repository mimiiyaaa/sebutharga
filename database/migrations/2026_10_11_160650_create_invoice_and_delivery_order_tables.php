<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_master', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->string('invoice_no', 100);
            $table->unsignedInteger('purchase_order_id')->nullable();
            $table->unsignedInteger('customer_id');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('sst_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_status', 50)->default('Belum Bayar');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('invoice_no');
            $table->index('purchase_order_id');
            $table->index('customer_id');
            $table->index('payment_status');
        });

        Schema::create('invoice_item', function (Blueprint $table) {
            $table->increments('invoice_item_id');
            $table->unsignedInteger('invoice_id');
            $table->text('item_description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('invoice_id');
        });

        Schema::create('delivery_order_master', function (Blueprint $table) {
            $table->increments('delivery_order_id');
            $table->string('do_no', 100);
            $table->unsignedInteger('purchase_order_id')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('customer_id');
            $table->date('do_date');
            $table->text('delivery_address');
            $table->string('recipient_name', 255)->nullable();
            $table->string('recipient_phone', 50)->nullable();
            $table->string('delivery_status', 50)->default('Draf');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('do_no');
            $table->index('purchase_order_id');
            $table->index('invoice_id');
            $table->index('customer_id');
            $table->index('delivery_status');
        });

        Schema::create('delivery_order_item', function (Blueprint $table) {
            $table->increments('do_item_id');
            $table->unsignedInteger('delivery_order_id');
            $table->text('item_description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('delivery_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_item');
        Schema::dropIfExists('delivery_order_master');
        Schema::dropIfExists('invoice_item');
        Schema::dropIfExists('invoice_master');
    }
};
