<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_item', function (Blueprint $table) {
            $table->increments('po_item_id');

            $table->unsignedInteger('purchase_order_id');

            $table->text('item_description');

            $table->integer('quantity')->default(0);

            $table->string('unit', 50);

            $table->decimal('unit_price', 12, 2)->default(0.00);

            $table->decimal('subtotal', 12, 2)->default(0.00);

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            // User ID
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->foreign('purchase_order_id')
                ->references('purchase_order_id')
                ->on('purchase_order_master');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_item');
    }
};