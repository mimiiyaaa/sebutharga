<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->decimal('sst_percent', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('sst_amount', 12, 2)->default(0)->after('sst_percent');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_master', function (Blueprint $table) {
            $table->dropColumn(['sst_percent', 'sst_amount']);
        });
    }
};
