<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('sebutharga_detail', function (Blueprint $table) {
            $table->longText('document_data')->nullable();
        });
    }
    public function down(): void {
        Schema::table('sebutharga_detail', fn (Blueprint $table) => $table->dropColumn('document_data'));
    }
};
