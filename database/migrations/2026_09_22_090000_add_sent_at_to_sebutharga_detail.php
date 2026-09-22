<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dateTime('sent_at')->nullable()->after('status_quotation');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};
