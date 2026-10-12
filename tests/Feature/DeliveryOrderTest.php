<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
    DB::purge('sqlite');
    Schema::create('customer_supplier', function (Blueprint $table) {
        $table->increments('customer_id'); $table->integer('jenis_customer');
        $table->string('company_name'); $table->string('customer_name'); $table->text('address');
    });
    Schema::create('companies', function (Blueprint $table) {
        $table->increments('company_id'); $table->string('nama_syarikat');
    });
    (require database_path('migrations/2026_10_11_160650_create_invoice_and_delivery_order_tables.php'))->up();
    (require database_path('migrations/2026_10_12_010000_add_delivery_order_document_data.php'))->up();
    DB::table('customer_supplier')->insert(['customer_id'=>1,'jenis_customer'=>1,'company_name'=>'Jabatan Teknologi & Komunikasi','customer_name'=>'Pelanggan','address'=>"Sektor Awam Negeri Kelantan\n15000 Kota Bharu, Kelantan."]);
    $user = new User(); $user->id = 42; $user->name = 'Tester';
    $this->actingAs($user);
    $this->doData = [
        'do_no'=>'K1R-DO-2026-001', 'do_date'=>'2026-08-11', 'customer_id'=>1,
        'issuer_name'=>'K ONE RESOURCES', 'issuer_address'=>"PT 2223, Tingkat 1, Jalan Long Yunus,\n15200 Kota Bharu, Kelantan.",
        'issuer_phone'=>'+60 19-242 8373', 'issuer_email'=>'wanswh@gmail.com',
        'customer_name'=>'Jabatan Teknologi & Komunikasi', 'customer_address'=>"Sektor Awam Negeri Kelantan\n15000 Kota Bharu, Kelantan.",
        'signature_company'=>'K ONE RESOURCES',
        'notes'=>'Nota: Sila pastikan kuantiti dan keadaan barangan adalah memuaskan sebelum menandatangani DO ini.',
        'items'=>[
            ['description'=>"Laptop Komersial Spesifikasi Tinggi\n(Intel i7, 16GB RAM, 512GB SSD, Windows 11 Pro)",'quantity'=>5,'unit'=>'Unit','checked'=>0],
            ['description'=>"Pemasangan & Setup Rangkaian Pejabat\n(Termasuk Konfigurasi Router, Switch, Access Point, & Kabel LAN)",'quantity'=>1,'unit'=>'Sesi','checked'=>0],
            ['description'=>"Pembangunan Aplikasi Sistem Inventori\n(Fasa 1 - Cloud Based Architecture)",'quantity'=>1,'unit'=>'Sistem','checked'=>0],
        ],
    ];
});


test('delivery order preserves list intermediate and create navigation', function () {
    $this->get(route('delivery-order.customer'))->assertOk()->assertViewIs('pages.delivery-order')
        ->assertSee('href="'.route('delivery-order.customer.create').'"',false)->assertSee('Jumlah Kuantiti');
    $this->get(route('delivery-order.customer.create'))->assertOk()->assertViewIs('pages.customer-document-start')
        ->assertSee('href="'.route('delivery-order.customer.form').'"',false);
    $form=$this->get(route('delivery-order.customer.form'))->assertOk()->assertSee('Cipta &amp; Simpan DO',false)
        ->assertSee('href="'.route('delivery-order.customer.create').'"',false)->assertDontSee('Harga Seunit');
    if(getenv('DO_VISUAL_CHECK')) file_put_contents(storage_path('app/do-form-check.html'),$form->getContent());
});

test('delivery order saves snapshot items and renders downloadable pdf', function () {
    $this->post(route('delivery-order.customer.store'),$this->doData)->assertSessionHasNoErrors()->assertRedirect(route('delivery-order.customer.preview',1));
    $this->assertDatabaseCount('delivery_order_master',1);
    $this->assertDatabaseCount('delivery_order_item',3);
    $this->assertDatabaseHas('delivery_order_master',['do_no'=>'K1R-DO-2026-001','created_by'=>42]);
    DB::table('customer_supplier')->where('customer_id',1)->update(['company_name'=>'Changed']);
    $this->get(route('delivery-order.customer'))->assertOk()->assertSee('Jabatan Teknologi');
    $this->get(route('delivery-order.customer.preview',1))->assertOk()->assertSee('Cetak / Buka PDF');
    $pdf=$this->get(route('delivery-order.customer.pdf',1))->assertOk()->assertHeader('content-type','application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF-');
    $this->get(route('delivery-order.customer.pdf',['id'=>1,'download'=>1]))->assertDownload('K1R-DO-2026-001.pdf');
    if(getenv('DO_VISUAL_CHECK')) file_put_contents(storage_path('app/do-sample-check.pdf'),$pdf->getContent());
    $this->post(route('delivery-order.customer.store'),$this->doData)->assertSessionHasErrors('do_no');
    $this->assertDatabaseCount('delivery_order_master',1);
});

test('delivery order validates customer quantities receipt date and checks without saving preview', function () {
    $this->post(route('delivery-order.customer.draft-preview'),$this->doData)->assertOk()->assertHeader('content-type','application/pdf');
    $this->assertDatabaseCount('delivery_order_master',0);
    foreach (['customer_id'=>999,'items.0.quantity'=>0,'items.0.checked'=>'invalid','received_date'=>'2026-08-10'] as $field=>$value) {
        $data=$this->doData; data_set($data,$field,$value);
        $this->post(route('delivery-order.customer.store'),$data)->assertSessionHasErrors($field);
    }
    $this->assertDatabaseCount('delivery_order_master',0);
    $this->get(route('delivery-order.customer.pdf',999))->assertNotFound();
});

test('delivery order supports independent signature name and checked items', function () {
    $data=$this->doData; $data['signature_company']='Nama Syarikat Manual'; $data['items'][0]['checked']=1;
    $this->post(route('delivery-order.customer.store'),$data)->assertSessionHasNoErrors();
    $record=DB::table('delivery_order_master')->first();
    $document=json_decode($record->document_data,true);
    expect($document['signature_company'])->toBe('Nama Syarikat Manual')->and($document['issuer_name'])->toBe('K ONE RESOURCES');
    $this->assertDatabaseHas('delivery_order_item',['do_item_id'=>1,'remarks'=>'/']);
    $this->assertDatabaseHas('delivery_order_item',['do_item_id'=>2,'remarks'=>null]);
});

test('long delivery order renders and anonymous access is blocked', function () {
    $data=$this->doData; $data['items']=array_fill(0,35,$data['items'][1]);
    $pdf=$this->post(route('delivery-order.customer.draft-preview'),$data)->assertOk();
    if(getenv('DO_VISUAL_CHECK')) file_put_contents(storage_path('app/do-long-check.pdf'),$pdf->getContent());
    auth()->forgetGuards();
    $this->get(route('delivery-order.customer.form'))->assertRedirect(route('login'));
    $this->post(route('delivery-order.customer.store'),$this->doData)->assertRedirect(route('login'));
});
