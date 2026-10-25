<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array']);
    DB::purge('sqlite');
    Storage::fake('local');
    Schema::create('users', function (Blueprint $table) {
        $table->increments('id'); $table->string('name');
        $table->string('email')->nullable(); $table->string('jawatan')->nullable();
    });
    Schema::create('customer_supplier', function (Blueprint $table) {
        $table->increments('customer_id'); $table->string('company_name');
    });
    Schema::create('invoice_master', function (Blueprint $table) {
        $table->increments('invoice_id'); $table->integer('customer_id');
        $table->string('invoice_no'); $table->string('invoice_title')->nullable();
        $table->string('document_kind'); $table->date('invoice_date'); $table->decimal('total_amount',12,2);
    });
    foreach (['2026_10_18_120000_create_payment_voucher_documents_table.php',
        '2026_10_18_130000_add_payment_method_to_payment_voucher_documents_table.php',
        '2026_10_22_110000_create_payment_voucher_invoice_table.php',
        '2026_10_22_120000_rename_payment_voucher_invoice_link.php'] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }
    DB::table('customer_supplier')->insert(['customer_id'=>1,'company_name'=>'Customer']);
    DB::table('invoice_master')->insert(['invoice_id'=>1,'customer_id'=>1,'invoice_no'=>'INV-001','invoice_title'=>'Equipment','document_kind'=>'customer','invoice_date'=>'2026-10-22','total_amount'=>150]);
    $user=new User(); $user->id=42; $user->name='Tester'; $this->actingAs($user);
});

test('voucher invoice selection save detail and deletion work after migrations', function () {
    $this->get(route('baucar-bayaran.index'))->assertOk();
    $this->get(route('baucar-bayaran.create'))->assertOk()->assertSee('INV-001')->assertSee('Equipment')
        ->assertSee('href="'.route('baucar-bayaran.form').'"',false);
    $this->get(route('baucar-bayaran.form'))->assertOk()->assertViewHas('selectedInvoiceIds',[]);
    $this->get(route('baucar-bayaran.form',['invoice_ids'=>[1]]))->assertOk()->assertSee('INV-001');
    $this->post(route('baucar-bayaran.store'),[
        'voucher_no'=>'PV-001','voucher_date'=>'2026-10-22','payee_name'=>'Payee',
        'payment_method'=>'Tunai','invoice_ids'=>[1], 'total_amount'=>1,
        'document_file'=>UploadedFile::fake()->createWithContent('voucher.pdf',"%PDF-1.4\n%%EOF"),
    ])->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.index'));
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'total_amount'=>150]);
    $this->assertDatabaseHas('payment_voucher_invoice',['payment_voucher_id'=>1,'invoice_id'=>1]);
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('INV-001')->assertSee('Equipment');
    $this->get(route('baucar-bayaran.file',1))->assertOk();
    $path=DB::table('payment_voucher_documents')->value('file_path');
    $this->delete(route('baucar-bayaran.delete',1))->assertRedirect(route('baucar-bayaran.index'));
    $this->assertDatabaseCount('payment_voucher_invoice',0);
    Storage::disk('local')->assertMissing($path);
});
