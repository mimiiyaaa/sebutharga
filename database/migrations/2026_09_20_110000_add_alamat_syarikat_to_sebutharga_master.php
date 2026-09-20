<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->text('alamat_syarikat')->nullable()->after('person_in_charge');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->dropColumn('alamat_syarikat');
        });
    }
};
