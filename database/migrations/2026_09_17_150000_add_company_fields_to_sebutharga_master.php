<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->string('nama_syarikat', 255)->nullable()->after('quotation_title');
            $table->string('no_telefon', 50)->nullable()->after('nama_syarikat');
            $table->string('emel', 255)->nullable()->after('no_telefon');
            $table->string('person_in_charge', 255)->nullable()->after('emel');
        });
    }

    public function down(): void
    {
        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->dropColumn(['nama_syarikat', 'no_telefon', 'emel', 'person_in_charge']);
        });
    }
};
