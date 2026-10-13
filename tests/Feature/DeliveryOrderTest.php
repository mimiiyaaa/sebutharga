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
    Schema::create('lo_inden_master', function (Blueprint $table) {
        $table->increments('lo_inden_id'); $table->string('lo_inden_no'); $table->integer('customer_id');
        $table->date('lo_inden_date'); $table->integer('quotation_id')->nullable(); $table->integer('quotation_detail_id')->nullable();
    });
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>1,'lo_inden_no'=>'LO-SELECT-001','customer_id'=>1,'lo_inden_date'=>'2026-10-13']);
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
        ->assertSee('action="'.route('delivery-order.customer.form').'"',false);
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
    $this->get(route('delivery-order.customer.preview',1))->assertOk()->assertSee('Cetak PDF')->assertSee('srcdoc=', false)->assertSee('target="_blank"', false);
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


test('delivery-order supports detail edit update and delete without replacing the record', function () {
    $this->post(route('delivery-order.customer.store'), $this->doData)->assertSessionHasNoErrors();
    $this->get(route('delivery-order.customer'))->assertOk()->assertSee('Lihat')->assertSee('Edit')->assertSee('Padam');
    $this->get(route('delivery-order.customer.show', 1))->assertOk()->assertSee('K ONE RESOURCES')->assertSee('Senarai Item');
    $this->get(route('delivery-order.customer.edit', 1))->assertOk()->assertSee('Simpan Perubahan')->assertSee($this->doData['do_no'])->assertViewHas('initialItems', fn ($items) => count($items) === 3);
    $data = $this->doData;
    $data['customer_name'] = 'Pelanggan Dikemas Kini';
    $data['items'] = [['description' => 'Item baharu', 'quantity' => 2, 'unit' => 'Unit', 'checked' => 1]];
    $this->put(route('delivery-order.customer.edit-preview', 1), $data)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->assertDatabaseCount('delivery_order_item', 3);
    $this->put(route('delivery-order.customer.update', 1), $data)->assertSessionHasNoErrors()->assertRedirect(route('delivery-order.customer.show', 1));
    $this->assertDatabaseCount('delivery_order_master', 1);
    $this->assertDatabaseCount('delivery_order_item', 1);
    $this->assertDatabaseHas('delivery_order_item', ['delivery_order_id' => 1, 'remarks' => '/']);
    $this->get(route('delivery-order.customer.show', 1))->assertOk()->assertSee('Pelanggan Dikemas Kini')->assertSee('Item baharu');
    $this->get(route('delivery-order.customer.preview', 1))->assertOk()->assertSee('Item baharu');
    $this->delete(route('delivery-order.customer.delete', 1))->assertRedirect(route('delivery-order.customer'));
    $this->assertDatabaseCount('delivery_order_master', 0);
    $this->assertDatabaseCount('delivery_order_item', 0);
    $this->get(route('delivery-order.customer.show', 1))->assertNotFound();
});

test('delivery-order rejects duplicate updates and invalid items and scopes customer actions', function () {
    $this->post(route('delivery-order.customer.store'), $this->doData);
    $second = $this->doData; $second['do_no'] .= '-2';
    $this->post(route('delivery-order.customer.store'), $second);
    $this->put(route('delivery-order.customer.update', 1), $second)->assertSessionHasErrors('do_no');
    $bad = $this->doData; $bad['items'][0]['quantity'] = -1;
    $this->put(route('delivery-order.customer.update', 1), $bad)->assertSessionHasErrors('items.0.quantity');
    $this->assertDatabaseCount('delivery_order_item', 6);
    DB::table('delivery_order_master')->where('delivery_order_id', 2)->update(['document_kind' => 'supplier']);
    $this->get(route('delivery-order.customer.show', 2))->assertNotFound();
    $this->get(route('delivery-order.customer.edit', 2))->assertNotFound();
    $this->put(route('delivery-order.customer.update', 2), $second)->assertNotFound();
    $this->delete(route('delivery-order.customer.delete', 2))->assertNotFound();
    $this->assertDatabaseCount('delivery_order_master', 2);
    auth()->logout();
    $this->get(route('delivery-order.customer.show', 1))->assertRedirect(route('login'));
    $this->put(route('delivery-order.customer.update', 1), $this->doData)->assertRedirect(route('login'));
    $this->delete(route('delivery-order.customer.delete', 1))->assertRedirect(route('login'));
});


test('delivery-order saves and displays LO reference independently from historical PO references', function () {
    $data = $this->doData; $data['lo_reference'] = 'LO-2026-123';
    $this->post(route('delivery-order.customer.store'), $data)->assertSessionHasNoErrors();
    $this->get(route('delivery-order.customer'))->assertOk()->assertSee('No. Local Order (LO)')->assertSee('LO-2026-123')->assertDontSee('No. Purchase Order');
    $this->get(route('delivery-order.customer.show', 1))->assertOk()->assertSee('LO-2026-123');
    $this->get(route('delivery-order.customer.edit', 1))->assertOk()->assertSee('LO-2026-123')->assertSee('name="lo_inden_id"', false);
    $this->get(route('delivery-order.customer.preview', 1))->assertOk()->assertDontSee('LO-2026-123')->assertDontSee('No. Rujukan LO:');
    $snapshot = json_decode(DB::table('delivery_order_master')->where('delivery_order_id', 1)->value('document_data'), true);
    $snapshot['po_reference'] = 'OLD-PO-999';
    DB::table('delivery_order_master')->where('delivery_order_id', 1)->update(['document_data' => json_encode($snapshot)]);
    $data['lo_reference'] = 'LO-2026-456';
    $this->put(route('delivery-order.customer.update', 1), $data)->assertSessionHasNoErrors();
    $snapshot = json_decode(DB::table('delivery_order_master')->where('delivery_order_id', 1)->value('document_data'), true);
    expect($snapshot['lo_reference'])->toBe('LO-2026-456');
    expect($snapshot['po_reference'])->toBe('OLD-PO-999');
    $this->get(route('delivery-order.customer'))->assertOk()->assertSee('LO-2026-456')->assertDontSee('OLD-PO-999');
});


test('delivery-order starts from selected LO and validates customer relationship', function () {
    $this->get(route('delivery-order.customer.create'))->assertOk()->assertSee('LO-SELECT-001')->assertSee('name="lo_inden_id"', false);
    $this->get(route('delivery-order.customer.form', ['lo_inden_id'=>1]))->assertOk()->assertViewHas('values', fn($values) => $values['lo_reference'] === 'LO-SELECT-001' && $values['customer_id'] === 1);
    $this->get(route('delivery-order.customer.form', ['lo_inden_id'=>999]))->assertSessionHasErrors('lo_inden_id');
    $data=$this->doData; $data['lo_inden_id']=1; $data['lo_reference']='FORGED';
    $this->post(route('delivery-order.customer.store'), $data)->assertSessionHasNoErrors();
    $this->get(route('delivery-order.customer.show', 1))->assertOk()->assertSee('LO-SELECT-001')->assertDontSee('FORGED');
    $data['customer_id']=999;
    $this->post(route('delivery-order.customer.store'), $data)->assertSessionHasErrors('customer_id');
});


test('delivery-order prefills items from the quotation linked to the selected LO', function () {
    Schema::create('sebutharga_detail', function (Blueprint $table) {
        $table->increments('quotation_detail_id'); $table->integer('quotation_id'); $table->text('document_data')->nullable();
    });
    Schema::create('sebutharga_item', function (Blueprint $table) {
        $table->increments('quotation_item_id'); $table->integer('quotation_detail_id');
        $table->string('item_description'); $table->decimal('quantity',12,2); $table->string('unit'); $table->decimal('unit_price',12,2);
    });
    DB::table('sebutharga_detail')->insert(['quotation_detail_id'=>7,'quotation_id'=>3,'document_data'=>json_encode(['company_name'=>'Pelanggan Snapshot','address'=>'Alamat Snapshot'])]);
    DB::table('sebutharga_item')->insert(['quotation_detail_id'=>7,'item_description'=>'Item daripada LO','quantity'=>2,'unit'=>'Unit','unit_price'=>125]);
    DB::table('lo_inden_master')->where('lo_inden_id',1)->update(['quotation_id'=>3,'quotation_detail_id'=>7]);
    $this->get(route('delivery-order.customer.form',['lo_inden_id'=>1]))->assertOk()
        ->assertViewHas('values',fn($values)=>$values['customer_name']==='Pelanggan Snapshot')
        ->assertViewHas('initialItems',fn($items)=>$items[0]['description']==='Item daripada LO' && (float)$items[0]['price']===125.0);
});


test('delivery-order can change its LO from the edit dropdown', function () {
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>2,'lo_inden_no'=>'LO-SELECT-002','customer_id'=>1,'lo_inden_date'=>'2026-10-13']);
    $data=$this->doData; $data['lo_inden_id']=1;
    $this->post(route('delivery-order.customer.store'),$data)->assertSessionHasNoErrors();
    $this->get(route('delivery-order.customer.edit',1))->assertOk()->assertSee('LO-SELECT-001')->assertSee('LO-SELECT-002')->assertSee('value="1" selected',false);
    $data['lo_inden_id']=2;
    $this->put(route('delivery-order.customer.update',1),$data)->assertSessionHasNoErrors();
    $document=json_decode(DB::table('delivery_order_master')->value('document_data'),true);
    expect((int)$document['lo_inden_id'])->toBe(2);
    expect($document['lo_reference'])->toBe('LO-SELECT-002');
    $this->get(route('delivery-order.customer'))->assertOk()->assertSee('LO-SELECT-002');
    $data['lo_inden_id']=999;
    $this->put(route('delivery-order.customer.update',1),$data)->assertSessionHasErrors('lo_inden_id');
});
