<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->string('cheque_no', 100)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->dropColumn('cheque_no');
        });
    }
};
