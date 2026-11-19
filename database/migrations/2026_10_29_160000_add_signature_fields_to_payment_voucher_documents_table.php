<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->string('prepared_by', 255)->nullable()->after('uploaded_by');
            $table->string('prepared_role', 255)->nullable()->after('prepared_by');
            $table->string('received_by', 255)->nullable()->after('prepared_role');
            $table->string('received_role', 255)->nullable()->after('received_by');
        });
    }

    public function down(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->dropColumn(['prepared_by', 'prepared_role', 'received_by', 'received_role']);
        });
    }
};
