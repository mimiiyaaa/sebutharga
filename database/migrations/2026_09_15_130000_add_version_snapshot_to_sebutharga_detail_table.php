<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->date('quotation_date')->nullable()->after('draft_no');
            $table->unsignedInteger('customer_id')->nullable()->after('quotation_date');
            $table->string('quotation_title', 255)->nullable()->after('customer_id');
            $table->string('no_rujukan_pelanggan', 100)->nullable()->after('quotation_title');
            $table->integer('status_quotation')->nullable()->after('no_rujukan_pelanggan');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_date', 'customer_id', 'quotation_title',
                'no_rujukan_pelanggan', 'status_quotation',
            ]);
        });
    }
};
