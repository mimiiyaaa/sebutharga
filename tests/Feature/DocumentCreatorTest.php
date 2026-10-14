<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Schema::create('users', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('jawatan')->nullable();
    });
});

test('document creator panel displays the original creator and creation date', function () {
    DB::table('users')->insert(['id' => 7, 'name' => 'Original Creator', 'email' => 'creator@example.test', 'jawatan' => 'Pengurus Besar']);
    $html = Blade::render('<x-common.document-creator :creator-id="7" created-at="2026-10-14 11:51:00" document-label="PO ini" />');
    expect($html)->toContain('Original Creator', 'creator@example.test', 'Pengurus Besar', '14/10/2026 · 11:51', 'PO ini');
});

test('document creator panel supports legacy records and deleted creators', function () {
    foreach ([null, 999] as $creatorId) {
        $html = Blade::render('<x-common.document-creator :creator-id="$creatorId" />', compact('creatorId'));
        expect($html)->toContain('Maklumat Login', '—');
    }
});
