<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->string('disediakan_role')->nullable()->after('disediakan_oleh');
            $table->string('diterima_role')->nullable()->after('diterima_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->dropColumn(['disediakan_role', 'diterima_role']);
        });
    }
};
