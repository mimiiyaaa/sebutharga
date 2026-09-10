<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_master', function (Blueprint $table) {
            $table->increments('quotation_id');

            $table->string('quotation_no', 50)->unique();

            $table->integer('customer_id');

            $table->date('quotation_date');

            $table->string('quotation_title', 255)->nullable();

            $table->string('no_rujukan_pelanggan', 100)->nullable();

            // 1 = berjaya, 0 = gagal
            $table->integer('status_quotation')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_master');
    }
};