<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sebutharga_detail', function (Blueprint $table) {
            $table->increments('quotation_detail_id');

            $table->unsignedInteger('quotation_id');

            $table->integer('draft_no')->default(1);

            $table->decimal('jumlah_total', 12, 2)->default(0.00);

            $table->text('terma_syarat')->nullable();

            $table->string('disediakan_oleh', 255)->nullable();

            $table->string('diterima_oleh', 255)->nullable();

            $table->string('status_draft', 20)->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('quotation_id')
                ->references('quotation_id')
                ->on('sebutharga_master');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sebutharga_detail');
    }
};