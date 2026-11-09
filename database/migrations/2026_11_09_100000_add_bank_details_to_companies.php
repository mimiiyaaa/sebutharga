<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('nama_bank', 255)->nullable()->after('alamat_syarikat');
            $table->string('nama_akaun', 255)->nullable()->after('nama_bank');
            $table->string('no_akaun', 100)->nullable()->after('nama_akaun');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nama_akaun', 'no_akaun']);
        });
    }
};
