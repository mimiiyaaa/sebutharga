<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_voucher_documents', function (Blueprint $table) {
            $table->string('supplier_payment_status', 20)->default('unpaid');
            $table->timestamp('supplier_status_changed_at')->nullable();
            $table->unsignedInteger('supplier_status_changed_by')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('payment_voucher_documents', fn (Blueprint $table) => $table->dropColumn(['supplier_payment_status', 'supplier_status_changed_at', 'supplier_status_changed_by']));
    }
};
