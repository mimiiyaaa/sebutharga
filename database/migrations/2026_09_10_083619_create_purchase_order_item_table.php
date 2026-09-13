<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_item', function (Blueprint $table) {

            // Primary Key
            $table->increments('po_item_id');

            // Reference to purchase_order_master
            // No Foreign Key Constraint
            $table->unsignedInteger('purchase_order_id');

            // Item Information
            $table->text('item_description');

            $table->integer('quantity')->default(0);

            $table->string('unit', 50);

            $table->decimal('unit_price', 12, 2)->default(0.00);

            $table->decimal('subtotal', 12, 2)->default(0.00);

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
        Schema::dropIfExists('purchase_order_item');
    }
};