<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lo_inden_master', function (Blueprint $table) {

            // Primary Key
            $table->increments('lo_inden_id');

            // LO / Indent Information
            $table->string('lo_inden_no', 100)->unique();

            // Reference to customer_supplier
            // No Foreign Key Constraint
            $table->unsignedInteger('customer_id');

            $table->date('lo_inden_date');

            $table->decimal('amount', 12, 2)->default(0.00);

            // Uploaded LO / Indent File
            $table->string('document_file', 255);

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
        Schema::dropIfExists('lo_inden_master');
    }
};