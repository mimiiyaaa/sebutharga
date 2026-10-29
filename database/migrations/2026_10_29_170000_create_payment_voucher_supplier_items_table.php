<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_voucher_supplier_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_voucher_id');
            $table->text('item_description');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();

            $table->index('payment_voucher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_supplier_item');
    }
};
