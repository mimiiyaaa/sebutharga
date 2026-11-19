<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('payment_voucher_invoice', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->nullable();
            $table->unsignedInteger('recorded_by')->nullable();
        });
    }
    public function down(): void {
        Schema::table('payment_voucher_invoice', fn (Blueprint $table) => $table->dropColumn(['amount', 'recorded_by']));
    }
};
