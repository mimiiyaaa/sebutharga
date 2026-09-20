<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembekal_quotation_master', function (Blueprint $table) {
            $table->increments('supplier_quotation_id');
            $table->unsignedInteger('quotation_id')->nullable();
            $table->unsignedInteger('supplier_id');
            $table->string('quotation_no_supplier', 100);
            $table->string('company_name', 255);
            $table->string('quotation_title', 255)->nullable();
            $table->string('person_in_charge', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('pembekal_quotation_item', function (Blueprint $table) {
            $table->increments('supplier_quotation_item_id');
            $table->unsignedInteger('supplier_quotation_id');
            $table->text('item_description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 50);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembekal_quotation_item');
        Schema::dropIfExists('pembekal_quotation_master');
    }
};
