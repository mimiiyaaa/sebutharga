<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array']);
    DB::purge('sqlite');
    Schema::create('customer_supplier', function (Blueprint $table) {
        $table->increments('customer_id'); $table->integer('jenis_customer');
        $table->string('company_name'); $table->string('customer_name')->nullable();
    });
    (require database_path('migrations/2026_09_20_130000_create_pembekal_quotation_tables.php'))->up();
    Schema::table('pembekal_quotation_master', function (Blueprint $table) {
        $table->date('quotation_date')->nullable(); $table->string('sumber_sebut_harga')->nullable();
    });
    DB::table('customer_supplier')->insert(['customer_id'=>2,'jenis_customer'=>2,'company_name'=>'Syarikat Tersimpan']);
    DB::table('pembekal_quotation_master')->insert(['supplier_quotation_id'=>6,'supplier_id'=>2,'company_name'=>'Syarikat Tersimpan','quotation_no_supplier'=>'SQ-006','quotation_date'=>'2026-10-14','sumber_sebut_harga'=>'E-mel','file_path'=>'pembekal-quotations/sample.pdf','file_name'=>'sample.pdf']);
    DB::table('pembekal_quotation_item')->insert(['supplier_quotation_id'=>6,'item_description'=>'Komputer','quantity'=>2,'unit'=>'Unit','unit_price'=>100,'subtotal'=>200]);
    $user=new User(); $user->id=42; $user->name='Tester'; $this->actingAs($user);
});

test('supplier quotation view leads to details before edit with saved supplier selected', function () {
    $this->get(route('sebut-harga-pembekal'))->assertOk()->assertSee('href="'.route('sebut-harga-pembekal.show',6).'"',false)->assertDontSee('href="'.route('sebut-harga-pembekal.edit',6).'"',false);
    $this->get(route('sebut-harga-pembekal.show',6))->assertOk()->assertSee('Syarikat Tersimpan')->assertSee('Komputer')->assertSee('200.00')->assertSee('sample.pdf')->assertSee('href="'.route('sebut-harga-pembekal.edit',6).'"',false);
    $this->get(route('sebut-harga-pembekal.edit',6))->assertOk()->assertSee('<option value="2" selected>Syarikat Tersimpan</option>',false)->assertSee('href="'.route('sebut-harga-pembekal.show',6).'"',false);
    $data=['supplier_id'=>2,'quotation_no_supplier'=>'SQ-006','quotation_date'=>'2026-10-14','sumber_sebut_harga'=>'E-mel','quotation_title'=>'Dikemas kini','items'=>[['description'=>'Komputer','quantity'=>3,'unit'=>'Unit','price'=>100]]];
    $this->post(route('sebut-harga-pembekal.save',6),$data)->assertSessionHasNoErrors()->assertRedirect(route('sebut-harga-pembekal.show',6));
    $this->assertDatabaseHas('pembekal_quotation_master',['supplier_quotation_id'=>6,'supplier_id'=>2,'company_name'=>'Syarikat Tersimpan','file_name'=>'sample.pdf']);
    $this->get(route('sebut-harga-pembekal.edit',6))->assertOk()->assertSee('<option value="2" selected>Syarikat Tersimpan</option>',false);
    $this->get(route('sebut-harga-pembekal.show',999))->assertNotFound();
});

test('supplier detail handles no attachment and requires login', function () {
    DB::table('pembekal_quotation_master')->where('supplier_quotation_id',6)->update(['file_path'=>null,'file_name'=>null]);
    $this->get(route('sebut-harga-pembekal.show',6))->assertOk()->assertSee('Tiada dokumen dimuat naik.');
    auth()->logout();
    $this->get(route('sebut-harga-pembekal.show',6))->assertRedirect(route('login'));
});

test('editing allows an untouched source while new quotations still require one', function () {
    $data=['supplier_id'=>2,'quotation_no_supplier'=>'SQ-006','quotation_date'=>'2026-10-14','items'=>[['description'=>'Komputer','quantity'=>2,'unit'=>'Unit','price'=>100]]];
    $this->get(route('sebut-harga-pembekal.edit',6))->assertOk()->assertDontSee('name="sumber_sebut_harga" required',false);
    $this->post(route('sebut-harga-pembekal.save',6),$data)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pembekal_quotation_master',['supplier_quotation_id'=>6,'sumber_sebut_harga'=>'E-mel']);
    $this->post(route('sebut-harga-pembekal.save',6),$data+['sumber_sebut_harga'=>''])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pembekal_quotation_master',['supplier_quotation_id'=>6,'sumber_sebut_harga'=>'E-mel']);
    DB::table('pembekal_quotation_master')->where('supplier_quotation_id',6)->update(['sumber_sebut_harga'=>null]);
    $this->post(route('sebut-harga-pembekal.save',6),$data)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pembekal_quotation_master',['supplier_quotation_id'=>6,'sumber_sebut_harga'=>null]);
    $this->post(route('sebut-harga-pembekal.save',6),$data+['sumber_sebut_harga'=>'WhatsApp'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pembekal_quotation_master',['supplier_quotation_id'=>6,'sumber_sebut_harga'=>'WhatsApp']);
    $this->post(route('sebut-harga-pembekal.save'),$data)->assertSessionHasErrors('sumber_sebut_harga');
    $this->post(route('sebut-harga-pembekal.save',6),$data+['sumber_sebut_harga'=>'invalid'])->assertSessionHasErrors('sumber_sebut_harga');
});
