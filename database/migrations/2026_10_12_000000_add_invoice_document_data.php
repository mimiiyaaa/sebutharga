<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_master', function (Blueprint $table) {
            $table->json('document_data')->nullable();
            $table->string('document_kind', 20)->default('customer');
            $table->unique('invoice_no', 'invoice_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_master', function (Blueprint $table) {
            $table->dropUnique('invoice_number_unique');
            $table->dropColumn(['document_data', 'document_kind']);
        });
    }
};
