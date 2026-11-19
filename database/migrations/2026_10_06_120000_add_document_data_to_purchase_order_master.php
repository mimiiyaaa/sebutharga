<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->json('document_data')->nullable()->after('terms_conditions');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->dropColumn('document_data');
        });
    }
};
