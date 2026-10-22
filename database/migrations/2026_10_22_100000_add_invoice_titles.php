<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_master', function (Blueprint $table) {
            $table->string('invoice_title', 255)->nullable()->after('invoice_date');
        });

        Schema::table('supplier_documents', function (Blueprint $table) {
            $table->string('document_title', 255)->nullable()->after('document_date');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_documents', function (Blueprint $table) {
            $table->dropColumn('document_title');
        });

        Schema::table('invoice_master', function (Blueprint $table) {
            $table->dropColumn('invoice_title');
        });
    }
};
