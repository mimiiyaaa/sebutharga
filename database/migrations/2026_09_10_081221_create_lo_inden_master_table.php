<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lo_inden_master', function (Blueprint $table) {
            $table->increments('lo_inden_id');

            $table->string('lo_inden_no', 100)->unique();

            $table->unsignedBigInteger('customer_id');

            $table->date('lo_inden_date');

            $table->decimal('amount', 12, 2)->default(0.00);

            $table->string('document_file', 255);

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customer_supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lo_inden_master');
    }
};