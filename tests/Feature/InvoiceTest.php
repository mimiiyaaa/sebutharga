<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
    DB::purge('sqlite');
    Schema::create('users', function (Blueprint $table) {
        $table->increments('id'); $table->string('name'); $table->string('email')->nullable(); $table->string('jawatan')->nullable();
    });
    DB::table('users')->insert(['id'=>42,'name'=>'Pencipta Asal','email'=>'creator@example.test','jawatan'=>'Pengurus']);
    Schema::create('customer_supplier', function (Blueprint $table) {
        $table->increments('customer_id'); $table->integer('jenis_customer');
        $table->string('company_name'); $table->string('customer_name'); $table->text('address');
    });
    Schema::create('companies', function (Blueprint $table) {
        $table->increments('company_id'); $table->string('nama_syarikat');
    });
    (require database_path('migrations/2026_10_11_160650_create_invoice_and_delivery_order_tables.php'))->up();
    (require database_path('migrations/2026_10_12_000000_add_invoice_document_data.php'))->up();
    Schema::table('invoice_master', fn (Blueprint $table) => $table->string('invoice_title')->nullable());
    foreach (['2026_10_18_120000_create_payment_voucher_documents_table.php', '2026_10_18_130000_add_payment_method_to_payment_voucher_documents_table.php', '2026_10_22_110000_create_payment_voucher_invoice_table.php', '2026_10_22_120000_rename_payment_voucher_invoice_link.php', '2026_10_26_100000_add_payment_allocations.php'] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }

    DB::table('customer_supplier')->insert(['customer_id'=>1,'jenis_customer'=>1,'company_name'=>'Jabatan Teknologi & Komunikasi','customer_name'=>'Pelanggan','address'=>"Sektor Awam Negeri Kelantan\n15000 Kota Bharu, Kelantan."]);
    Schema::create('sebutharga_master', function (Blueprint $table) {
        $table->increments('quotation_id'); $table->string('quotation_no')->nullable();
    });
    Schema::create('sebutharga_detail', function (Blueprint $table) {
        $table->increments('quotation_detail_id'); $table->integer('quotation_id'); $table->text('document_data')->nullable();
        $table->decimal('jumlah_total',12,2)->nullable(); $table->date('quotation_date')->nullable(); $table->string('quotation_title')->nullable();
    });
    Schema::create('lo_inden_master', function (Blueprint $table) {
        $table->increments('lo_inden_id'); $table->string('lo_inden_no'); $table->integer('customer_id');
        $table->date('lo_inden_date'); $table->decimal('amount',12,2)->default(0); $table->integer('quotation_id')->nullable(); $table->integer('quotation_detail_id')->nullable();
    });
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>1,'lo_inden_no'=>'LO-SELECT-001','customer_id'=>1,'lo_inden_date'=>'2026-10-13']);
    $user = new User(); $user->id = 42; $user->name = 'Tester';
    $this->actingAs($user);
    $this->invoiceData = [
        'invoice_no'=>'K1R-IV-2026-001', 'invoice_date'=>'2026-08-11', 'customer_id'=>1,
        'issuer_name'=>'K ONE RESOURCES', 'issuer_address'=>"PT 2223, Tingkat 1, Jalan Long Yunus,\n15200 Kota Bharu, Kelantan.",
        'issuer_phone'=>'+60 19-242 8373', 'issuer_email'=>'wanswh@gmail.com',
        'customer_name'=>'Jabatan Teknologi & Komunikasi', 'customer_address'=>"Sektor Awam Negeri Kelantan\n15000 Kota Bharu, Kelantan.",
        'bank_name'=>'Malayan Banking Berhad (Maybank)', 'account_name'=>'K ONE RESOURCES', 'account_no'=>'5530-XXXX-XXXX',
        'payment_instructions'=>'Sila lakukan pembayaran ke akaun bank korporat kami seperti tertera di bawah:', 'prepared_role'=>'Pengurus Besar',
        'items'=>[
            ['description'=>"Laptop Komersial Spesifikasi Tinggi\n(Intel i7, 16GB RAM, 512GB SSD, Windows 11 Pro)",'quantity'=>5,'unit'=>'Unit','price'=>3500],
            ['description'=>"Pemasangan & Setup Rangkaian Pejabat\n(Termasuk Konfigurasi Router, Switch, Access Point, & Kabel LAN)",'quantity'=>1,'unit'=>'Sesi','price'=>2500],
            ['description'=>"Pembangunan Aplikasi Sistem Inventori\n(Fasa 1 - Cloud Based Architecture)",'quantity'=>1,'unit'=>'Sistem','price'=>5000],
        ],
    ];
});

test('invoice creation saves snapshot and items with server calculated total', function () {
    $form = $this->get(route('invoice.customer.form'))->assertOk()->assertSee('Cipta &amp; Simpan Invois', false);
    if (getenv('INVOICE_VISUAL_CHECK')) file_put_contents(storage_path('app/invoice-form-check.html'), $form->getContent());
    $response = $this->post(route('invoice.customer.store'), $this->invoiceData + ['total_amount'=>1]);
    $response->assertSessionHasNoErrors()->assertRedirect(route('invoice.customer'));
    $this->assertDatabaseHas('invoice_master',['invoice_id'=>1,'total_amount'=>25000,'created_by'=>42]);
    $this->assertDatabaseCount('invoice_item',3);
    DB::table('customer_supplier')->where('customer_id',1)->update(['company_name'=>'Changed']);
    $this->get(route('invoice.customer'))->assertOk()->assertSee('Jabatan Teknologi');
    $this->get(route('invoice.customer.preview',1))->assertOk()->assertSee('Cetak PDF')->assertSee('srcdoc=', false)->assertSee('target="_blank"', false);
    $pdf = $this->get(route('invoice.customer.pdf',1))->assertOk()->assertHeader('content-type','application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF-');
    if (getenv('INVOICE_VISUAL_CHECK')) file_put_contents(storage_path('app/invoice-sample-check.pdf'),$pdf->getContent());
    $this->get(route('invoice.customer.pdf',['id'=>1,'download'=>1]))->assertDownload('K1R-IV-2026-001.pdf');
    $this->post(route('invoice.customer.store'),$this->invoiceData)->assertSessionHasErrors('invoice_no');
    $this->assertDatabaseCount('invoice_master',1);
});

test('draft preview produces pdf without saving and validates input', function () {
    $pdf=$this->post(route('invoice.customer.draft-preview'),$this->invoiceData)->assertOk()->assertHeader('content-type','application/pdf');
    $this->assertDatabaseCount('invoice_master',0);
    $bad=$this->invoiceData; $bad['items'][0]['quantity']=-1;
    $this->post(route('invoice.customer.store'),$bad)->assertSessionHasErrors('items.0.quantity');
    $bad=$this->invoiceData; $bad['customer_id']=999;
    $this->post(route('invoice.customer.store'),$bad)->assertSessionHasErrors('customer_id');
    $this->assertDatabaseCount('invoice_master',0);
    $this->get(route('invoice.customer.pdf',999))->assertNotFound();
});

test('long invoice can render multiple pages', function () {
    $data=$this->invoiceData; $data['items']=array_fill(0,30,$data['items'][1]);
    $pdf=$this->post(route('invoice.customer.draft-preview'),$data)->assertOk();
    if(getenv('INVOICE_VISUAL_CHECK')) file_put_contents(storage_path('app/invoice-long-check.pdf'),$pdf->getContent());
});

test('invoice routes require authentication', function () {
    auth()->forgetGuards();
    $this->get(route('invoice.customer.form'))->assertRedirect(route('login'));
    $this->post(route('invoice.customer.store'),$this->invoiceData)->assertRedirect(route('login'));
});



test('customer invoice keeps the original table and intermediate create page', function () {
    $this->get(route('invoice.customer'))->assertOk()->assertViewIs('pages.invoice')
        ->assertSee('filters.search', false)
        ->assertSee('href="'.route('invoice.customer.create').'"', false)
        ->assertDontSee('action="'.route('invoice.customer.form').'"', false);
    $this->get(route('invoice.customer.create'))->assertOk()->assertViewIs('pages.customer-document-start')
        ->assertSee('action="'.route('invoice.customer.form').'"', false);
    $this->get(route('invoice.customer.form'))->assertOk()
        ->assertSee('href="'.route('invoice.customer.create').'"', false);
});

test('signature company supports typed names and saved company choices independently of the issuer', function () {
    DB::table('companies')->insert(['nama_syarikat'=>'Pilihan Syarikat Sdn Bhd']);
    $this->get(route('invoice.customer.form'))->assertOk()->assertSee('signature-company-options')->assertSee('Pilihan Syarikat Sdn Bhd');
    foreach (['Nama Syarikat Manual', 'Pilihan Syarikat Sdn Bhd'] as $index => $name) {
        $data = $this->invoiceData;
        $data['invoice_no'] = 'SIGNATURE-'.($index + 1);
        $data['signature_company'] = $name;
        $this->post(route('invoice.customer.store'), $data)->assertSessionHasNoErrors();
        $record = DB::table('invoice_master')->where('invoice_no', $data['invoice_no'])->first();
        $document = json_decode($record->document_data, true);
        expect($document['signature_company'])->toBe($name)->and($document['issuer_name'])->toBe('K ONE RESOURCES');
        $html = view('pages.invoice.pdf', ['invoice'=>$record, 'document'=>$document, 'items'=>collect()])->render();
        expect($html)->toContain('Pengurus Besar<br>'.$name);
    }
});


test('invoice supports detail edit update and delete without replacing the record', function () {
    $this->post(route('invoice.customer.store'), $this->invoiceData)->assertSessionHasNoErrors();
    $this->get(route('invoice.customer'))->assertOk()->assertSee('Lihat')->assertSee('Edit')->assertSee('Padam');
    $this->get(route('invoice.customer.show', 1))->assertOk()->assertSee('Pencipta Asal')->assertSee('creator@example.test')->assertSee('Pengurus')->assertSee('K ONE RESOURCES')->assertSee('Senarai Item');
    $this->get(route('invoice.customer.edit', 1))->assertOk()->assertSee('Simpan Perubahan')->assertSee($this->invoiceData['invoice_no'])->assertViewHas('initialItems', fn ($items) => count($items) === 3);
    $data = $this->invoiceData;
    $data['customer_name'] = 'Pelanggan Dikemas Kini';
    $data['items'] = [['description' => 'Item baharu', 'quantity' => 2, 'unit' => 'Unit', 'price' => 125.50]];
    $this->put(route('invoice.customer.edit-preview', 1), $data)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->assertDatabaseCount('invoice_item', 3);
    $this->put(route('invoice.customer.update', 1), $data)->assertSessionHasNoErrors()->assertRedirect(route('invoice.customer.show', 1));
    $this->assertDatabaseCount('invoice_master', 1);
    $this->assertDatabaseCount('invoice_item', 1);
    $this->assertDatabaseHas('invoice_master', ['invoice_id' => 1, 'total_amount' => 251, 'updated_by' => 42]);
    $this->get(route('invoice.customer.show', 1))->assertOk()->assertSee('Pelanggan Dikemas Kini')->assertSee('Item baharu');
    $this->get(route('invoice.customer.preview', 1))->assertOk()->assertSee('Item baharu');
    $this->delete(route('invoice.customer.delete', 1))->assertRedirect(route('invoice.customer'));
    $this->assertDatabaseCount('invoice_master', 0);
    $this->assertDatabaseCount('invoice_item', 0);
    $this->get(route('invoice.customer.show', 1))->assertNotFound();
});

test('invoice rejects duplicate updates and invalid items and scopes customer actions', function () {
    $this->post(route('invoice.customer.store'), $this->invoiceData);
    $second = $this->invoiceData; $second['invoice_no'] .= '-2';
    $this->post(route('invoice.customer.store'), $second);
    $this->put(route('invoice.customer.update', 1), $second)->assertSessionHasErrors('invoice_no');
    $bad = $this->invoiceData; $bad['items'][0]['quantity'] = -1;
    $this->put(route('invoice.customer.update', 1), $bad)->assertSessionHasErrors('items.0.quantity');
    $this->assertDatabaseCount('invoice_item', 6);
    DB::table('invoice_master')->where('invoice_id', 2)->update(['document_kind' => 'supplier']);
    $this->get(route('invoice.customer.show', 2))->assertNotFound();
    $this->get(route('invoice.customer.edit', 2))->assertNotFound();
    $this->put(route('invoice.customer.update', 2), $second)->assertNotFound();
    $this->delete(route('invoice.customer.delete', 2))->assertNotFound();
    $this->assertDatabaseCount('invoice_master', 2);
    auth()->logout();
    $this->get(route('invoice.customer.show', 1))->assertRedirect(route('login'));
    $this->put(route('invoice.customer.update', 1), $this->invoiceData)->assertRedirect(route('login'));
    $this->delete(route('invoice.customer.delete', 1))->assertRedirect(route('login'));
});

test('invoice linked to a delivery order cannot be deleted leaving a dangling reference', function () {
    $this->post(route('invoice.customer.store'), $this->invoiceData)->assertSessionHasNoErrors();
    DB::table('delivery_order_master')->insert(['do_no' => 'LINKED-DO', 'invoice_id' => 1, 'customer_id' => 1, 'do_date' => '2026-08-11', 'delivery_address' => 'Alamat pelanggan']);
    $this->delete(route('invoice.customer.delete', 1))->assertSessionHasErrors('document');
    $this->assertDatabaseCount('invoice_master', 1);
    $this->assertDatabaseCount('invoice_item', 3);
});


test('invoice saves and displays LO reference independently from historical PO references', function () {
    $data = $this->invoiceData; $data['lo_reference'] = 'LO-2026-123';
    $this->post(route('invoice.customer.store'), $data)->assertSessionHasNoErrors();
    $this->get(route('invoice.customer'))->assertOk()->assertSee('No. Local Order (LO)')->assertSee('LO-2026-123')->assertDontSee('No. Purchase Order');
    $this->get(route('invoice.customer.show', 1))->assertOk()->assertSee('LO-2026-123');
    $this->get(route('invoice.customer.edit', 1))->assertOk()->assertSee('LO-2026-123')->assertSee('name="lo_inden_id"', false);
    $this->get(route('invoice.customer.preview', 1))->assertOk()->assertDontSee('LO-2026-123')->assertDontSee('No. Rujukan LO:');
    $snapshot = json_decode(DB::table('invoice_master')->where('invoice_id', 1)->value('document_data'), true);
    $snapshot['po_reference'] = 'OLD-PO-999';
    DB::table('invoice_master')->where('invoice_id', 1)->update(['document_data' => json_encode($snapshot)]);
    $data['lo_reference'] = 'LO-2026-456';
    $this->put(route('invoice.customer.update', 1), $data)->assertSessionHasNoErrors();
    $snapshot = json_decode(DB::table('invoice_master')->where('invoice_id', 1)->value('document_data'), true);
    expect($snapshot['lo_reference'])->toBe('LO-2026-456');
    expect($snapshot['po_reference'])->toBe('OLD-PO-999');
    $this->get(route('invoice.customer'))->assertOk()->assertSee('LO-2026-456')->assertDontSee('OLD-PO-999');
});


test('invoice starts from selected LO and validates customer relationship', function () {
    $this->get(route('invoice.customer.create'))->assertOk()->assertSee('LO-SELECT-001')->assertSee('name="lo_inden_id"', false);
    $this->get(route('invoice.customer.form', ['lo_inden_id'=>1]))->assertOk()->assertViewHas('values', fn($values) => $values['lo_reference'] === 'LO-SELECT-001' && $values['customer_id'] === 1);
    $this->get(route('invoice.customer.form', ['lo_inden_id'=>999]))->assertSessionHasErrors('lo_inden_id');
    $data=$this->invoiceData; $data['lo_inden_id']=1; $data['lo_reference']='FORGED';
    $this->post(route('invoice.customer.store'), $data)->assertSessionHasNoErrors();
    $this->get(route('invoice.customer.show', 1))->assertOk()->assertSee('LO-SELECT-001')->assertDontSee('FORGED');
    $data['customer_id']=999;
    $this->post(route('invoice.customer.store'), $data)->assertSessionHasErrors('customer_id');
});


test('invoice prefills items from the quotation linked to the selected LO', function () {
    Schema::create('sebutharga_item', function (Blueprint $table) {
        $table->increments('quotation_item_id'); $table->integer('quotation_detail_id');
        $table->string('item_description'); $table->decimal('quantity',12,2); $table->string('unit'); $table->decimal('unit_price',12,2);
    });
    DB::table('sebutharga_detail')->insert(['quotation_detail_id'=>7,'quotation_id'=>3,'document_data'=>json_encode(['company_name'=>'Pelanggan Snapshot','address'=>'Alamat Snapshot'])]);
    DB::table('sebutharga_item')->insert(['quotation_detail_id'=>7,'item_description'=>'Item daripada LO','quantity'=>2,'unit'=>'Unit','unit_price'=>125]);
    DB::table('lo_inden_master')->where('lo_inden_id',1)->update(['quotation_id'=>3,'quotation_detail_id'=>7]);
    $this->get(route('invoice.customer.form',['lo_inden_id'=>1]))->assertOk()
        ->assertViewHas('values',fn($values)=>$values['customer_name']==='Pelanggan Snapshot')
        ->assertViewHas('initialItems',fn($items)=>$items[0]['description']==='Item daripada LO' && (float)$items[0]['price']===125.0);
});


test('invoice can change its LO from the edit dropdown', function () {
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>2,'lo_inden_no'=>'LO-SELECT-002','customer_id'=>1,'lo_inden_date'=>'2026-10-13']);
    $data=$this->invoiceData; $data['lo_inden_id']=1;
    $this->post(route('invoice.customer.store'),$data)->assertSessionHasNoErrors();
    $this->get(route('invoice.customer.edit',1))->assertOk()->assertSee('LO-SELECT-001')->assertSee('LO-SELECT-002')->assertSee('value="1" selected',false);
    $data['lo_inden_id']=2;
    $this->put(route('invoice.customer.update',1),$data)->assertSessionHasNoErrors();
    $document=json_decode(DB::table('invoice_master')->value('document_data'),true);
    expect((int)$document['lo_inden_id'])->toBe(2);
    expect($document['lo_reference'])->toBe('LO-SELECT-002');
    $this->get(route('invoice.customer'))->assertOk()->assertSee('LO-SELECT-002');
    $data['lo_inden_id']=999;
    $this->put(route('invoice.customer.update',1),$data)->assertSessionHasErrors('lo_inden_id');
});

test('customer invoice list uses quotation information from the linked LO like delivery orders', function () {
    $this->post(route('invoice.customer.store'), $this->invoiceData)->assertSessionHasNoErrors();
    DB::table('sebutharga_detail')->insert(['quotation_detail_id'=>9,'quotation_id'=>1,'quotation_title'=>'Bekalan komputer','jumlah_total'=>1234.50]);
    DB::table('lo_inden_master')->where('lo_inden_id',1)->update(['quotation_detail_id'=>9,'amount'=>1000]);
    $invoice=DB::table('invoice_master')->first();
    $document=json_decode($invoice->document_data,true); $document['lo_reference']='LO-SELECT-001';
    DB::table('invoice_master')->where('invoice_id',$invoice->invoice_id)->update(['document_data'=>json_encode($document)]);
    $this->get(route('invoice.customer'))->assertOk()->assertSee('Tajuk Invois')->assertSee('Jumlah Harga (RM)')
        ->assertDontSee('>Jumlah (RM)<', false)
        ->assertViewHas('invoices', fn ($rows) => $rows[0]['quotation_title']==='Bekalan komputer' && $rows[0]['amount']===25000.0);
    DB::table('sebutharga_detail')->where('quotation_detail_id',9)->delete();
    $this->get(route('invoice.customer'))->assertOk()
        ->assertViewHas('invoices', fn ($rows) => $rows[0]['quotation_title']==='—' && $rows[0]['amount']===25000.0);
    $this->assertDatabaseHas('invoice_master',['invoice_id'=>$invoice->invoice_id,'total_amount'=>25000]);
});

test('invoice edit changes LO customer and preserves date without a PDF button', function () {
    $this->post(route('invoice.customer.store'),$this->invoiceData)->assertSessionHasNoErrors();
    DB::table('customer_supplier')->insert(['customer_id'=>2,'jenis_customer'=>1,'company_name'=>'Pelanggan Baharu','customer_name'=>'Pegawai','address'=>'Alamat Baharu']);
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>2,'lo_inden_no'=>'LO-NEW','customer_id'=>2,'lo_inden_date'=>'2026-10-14']);
    $this->get(route('invoice.customer.edit',1))->assertOk()->assertDontSee('Pratonton PDF')
        ->assertSee('value="2026-08-11"',false)
        ->assertViewHas('localOrders',fn($orders)=>(int)$orders->firstWhere('lo_inden_id',2)->customer_id===2);
    $data=$this->invoiceData + ['lo_inden_id'=>2];
    $data['customer_id']=2; $data['customer_name']='Pelanggan Baharu'; $data['customer_address']='Alamat Baharu';
    $this->put(route('invoice.customer.update',1),$data)->assertSessionHasNoErrors();
    $invoice=DB::table('invoice_master')->first();
    $document=json_decode($invoice->document_data,true);
    expect($invoice->customer_id)->toBe(2)->and($document['lo_reference'])->toBe('LO-NEW')
        ->and($document['customer_name'])->toBe('Pelanggan Baharu')->and($document['customer_address'])->toBe('Alamat Baharu');
    $this->assertDatabaseHas('invoice_master',['invoice_id'=>1,'total_amount'=>25000]);
});


test('customer invoice shows receipt history and protects linked amounts and deletion', function () {
    $this->post(route('invoice.customer.store'), $this->invoiceData)->assertSessionHasNoErrors();
    \Illuminate\Support\Facades\Storage::fake('local');
    $this->post(route('baucar-bayaran.store'), [
        'voucher_no'=>'RECEIPT-01', 'voucher_date'=>'2026-10-26', 'payee_name'=>'Pelanggan',
        'payment_method'=>'Tunai', 'invoice_ids'=>[1], 'allocations'=>[1=>5000], 'total_amount'=>5000,
        'document_file'=>\Illuminate\Http\UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\n%%EOF"),
    ])->assertSessionHasNoErrors();
    $this->get(route('invoice.customer.show',1))->assertOk()->assertSee(route('invoice.customer.payments',1))->assertDontSee('Sejarah Bayaran');
    $this->get(route('invoice.customer.payments',1))->assertOk()->assertSee('Bayaran Sebahagian')->assertSee('RECEIPT-01')->assertSee('20,000.00')->assertSee('Rekod Bayaran');
    $this->get(route('invoice.customer'))->assertOk()->assertViewHas('invoices', fn ($rows) => $rows[0]['balance_amount'] == 20000 && $rows[0]['paid_amount'] == 5000);
    $data = $this->invoiceData; $data['items'][0]['price'] = 1;
    $this->put(route('invoice.customer.update',1), $data)->assertSessionHasErrors('items');
    $this->delete(route('invoice.customer.delete',1))->assertSessionHasErrors('document');
    $this->assertDatabaseHas('invoice_master',['invoice_id'=>1,'total_amount'=>25000]);
});
