<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('company_id');
            $table->string('nama_syarikat', 255)->unique();
            $table->string('no_telefon', 50)->nullable();
            $table->string('emel', 255)->nullable();
            $table->string('person_in_charge', 255)->nullable();
            $table->text('alamat_syarikat')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        DB::table('sebutharga_master')
            ->select('nama_syarikat', 'no_telefon', 'emel', 'person_in_charge', 'alamat_syarikat')
            ->whereNotNull('nama_syarikat')
            ->where('nama_syarikat', '<>', '')
            ->orderBy('quotation_id')
            ->get()
            ->unique('nama_syarikat')
            ->each(function ($company) {
                DB::table('companies')->insert([
                    'nama_syarikat' => $company->nama_syarikat,
                    'no_telefon' => $company->no_telefon,
                    'emel' => $company->emel,
                    'person_in_charge' => $company->person_in_charge,
                    'alamat_syarikat' => $company->alamat_syarikat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->nullable()->after('customer_id');
        });

        DB::table('sebutharga_master as quotation')
            ->join('companies', 'companies.nama_syarikat', '=', 'quotation.nama_syarikat')
            ->update(['quotation.company_id' => DB::raw('companies.company_id')]);
    }

    public function down(): void
    {
        Schema::table('sebutharga_master', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });

        Schema::dropIfExists('companies');
    }
};
