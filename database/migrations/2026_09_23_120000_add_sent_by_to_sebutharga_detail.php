<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->string('sent_by', 255)->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn('sent_by');
        });
    }
};
