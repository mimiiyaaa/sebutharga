<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_item', function (Blueprint $table) {

            // Primary Key
            $table->increments('quotation_item_id');

            // Reference to sebutharga_detail
            // No Foreign Key Constraint
            $table->unsignedInteger('quotation_detail_id');

            // Item Information
            $table->text('item_description');

            $table->integer('quantity')->default(0);

            $table->string('unit', 50);

            $table->decimal('unit_price', 12, 2)->default(0.00);

            $table->decimal('subtotal', 12, 2)->default(0.00);

            // Audit Log
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            // created_at & updated_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_item');
    }
};