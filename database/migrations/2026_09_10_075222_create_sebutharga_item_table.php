<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_item', function (Blueprint $table) {
            $table->increments('quotation_item_id');

            $table->unsignedInteger('quotation_detail_id');

            $table->text('item_description');

            $table->integer('quantity')->default(0);

            $table->string('unit', 50);

            $table->decimal('unit_price', 12, 2)->default(0.00);

            $table->decimal('subtotal', 12, 2)->default(0.00);

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('quotation_detail_id')
                ->references('quotation_detail_id')
                ->on('sebutharga_detail');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_item');
    }
};