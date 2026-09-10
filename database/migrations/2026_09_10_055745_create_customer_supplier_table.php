<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_supplier', function (Blueprint $table) {
            $table->id('customer_id');

            $table->string('customer_code', 50)->unique();

            // 1 = Customer, 2 = Supplier
            $table->unsignedTinyInteger('jenis_customer');

            $table->string('customer_name', 255)->nullable();
            $table->string('company_name', 255);

            $table->text('address')->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('email', 255)->nullable();

            // Nama pegawai untuk dihubungi
            $table->string('attention_to', 255)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_supplier');
    }
};