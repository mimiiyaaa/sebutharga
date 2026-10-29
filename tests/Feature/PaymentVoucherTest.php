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
        $table->text('document_data')->nullable();
    });
    foreach (['2026_10_18_120000_create_payment_voucher_documents_table.php',
        '2026_10_18_130000_add_payment_method_to_payment_voucher_documents_table.php',
        '2026_10_22_110000_create_payment_voucher_invoice_table.php',
        '2026_10_22_120000_rename_payment_voucher_invoice_link.php',
        '2026_10_26_100000_add_payment_allocations.php', '2026_10_28_120000_add_supplier_type_to_payment_voucher_documents_table.php', '2026_10_28_140000_link_supplier_payment_vouchers_to_quotations.php', '2026_10_29_160000_add_signature_fields_to_payment_voucher_documents_table.php', '2026_10_29_170000_create_payment_voucher_supplier_items_table.php', '2026_10_29_180000_add_cheque_number_to_payment_voucher_documents_table.php'] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }
    DB::table('customer_supplier')->insert(['customer_id'=>1,'company_name'=>'Customer']);
    DB::table('invoice_master')->insert(['invoice_id'=>1,'customer_id'=>1,'invoice_no'=>'INV-001','invoice_title'=>'Equipment','document_kind'=>'customer','invoice_date'=>'2026-10-22','total_amount'=>150]);
    $user=new User(); $user->id=42; $user->name='Tester'; $this->actingAs($user);
});

test('voucher invoice selection save detail and deletion work after migrations', function () {
    $this->get(route('baucar-bayaran.index'))->assertOk();
    $this->get(route('baucar-bayaran.create'))->assertOk()->assertSee('INV-001')->assertSee('Equipment');
    $this->get(route('baucar-bayaran.form'))->assertRedirect(route('baucar-bayaran.create'));
    $this->get(route('baucar-bayaran.form',['invoice_ids'=>[1]]))->assertOk()->assertSee('INV-001');
    $this->post(route('baucar-bayaran.store'),[
        'voucher_no'=>'PV-001','voucher_date'=>'2026-10-22','payee_name'=>'Payee',
        'payment_method'=>'Tunai','invoice_ids'=>[1], 'total_amount'=>50, 'allocations'=>[1=>50],
        'document_file'=>UploadedFile::fake()->createWithContent('voucher.pdf',"%PDF-1.4\n%%EOF"),
    ])->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.index'));
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'total_amount'=>50]);
    $this->assertDatabaseHas('payment_voucher_invoice',['payment_voucher_id'=>1,'invoice_id'=>1,'amount'=>50]);
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('INV-001')->assertSee('Equipment');
    $this->get(route('baucar-bayaran.file',1))->assertOk();
    $path=DB::table('payment_voucher_documents')->value('file_path');
    $this->delete(route('baucar-bayaran.delete',1))->assertRedirect(route('baucar-bayaran.index'));
    $this->assertDatabaseCount('payment_voucher_invoice',0);
    Storage::disk('local')->assertMissing($path);
});

function receiptPayload(string $number, array $amounts, $total): array {
    return ['voucher_no'=>$number, 'voucher_date'=>'2026-10-26', 'payee_name'=>'Customer',
        'payment_method'=>'Pindahan Bank', 'invoice_ids'=>array_keys($amounts), 'allocations'=>$amounts,
        'total_amount'=>$total, 'document_file'=>UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\n%%EOF")];
}

test('partial receipts settle an invoice and deletion recalculates its balance', function () {
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>50],50))->assertSessionHasNoErrors();
    expect(\App\Services\InvoicePayments::summary(1,150))->toMatchArray(['paid_amount'=>50, 'balance_amount'=>100, 'payment_status'=>'Bayaran Sebahagian']);
    $this->post(route('baucar-bayaran.store'), receiptPayload('B',[1=>100],100))->assertSessionHasNoErrors();
    expect(\App\Services\InvoicePayments::summary(1,150))->toMatchArray(['paid_amount'=>150, 'balance_amount'=>0, 'payment_status'=>'Selesai Dibayar']);
    $this->delete(route('baucar-bayaran.delete',2))->assertSessionHasNoErrors();
    expect(\App\Services\InvoicePayments::summary(1,150)['balance_amount'])->toEqual(100);
});

test('rejects overpayments mismatched allocations duplicate references and foreign customer invoices', function () {
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>151],151))->assertSessionHasErrors('allocations.1');
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>50],60))->assertSessionHasErrors('total_amount');
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>0],0))->assertSessionHasErrors();
    $this->assertDatabaseCount('payment_voucher_documents',0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>50],50))->assertSessionHasNoErrors();
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>50],50))->assertSessionHasErrors('voucher_no');
    DB::table('invoice_master')->insert(['invoice_id'=>2,'customer_id'=>2,'invoice_no'=>'INV-002','document_kind'=>'customer','invoice_date'=>'2026-10-26','total_amount'=>100]);
    $this->post(route('baucar-bayaran.store'), receiptPayload('C',[1=>20,2=>30],50))->assertSessionHasErrors('invoice_ids');
    DB::table('invoice_master')->where('invoice_id',2)->update(['customer_id'=>1,'document_kind'=>'supplier']);
    $this->post(route('baucar-bayaran.store'), receiptPayload('C',[2=>30],30))->assertSessionHasErrors('invoice_ids.0');
});

test('one voucher allocates distinct amounts across invoices of the same customer', function () {
    DB::table('invoice_master')->insert(['invoice_id'=>2,'customer_id'=>1,'invoice_no'=>'INV-002','document_kind'=>'customer','invoice_date'=>'2026-10-26','total_amount'=>100]);
    $this->post(route('baucar-bayaran.store'), receiptPayload('A',[1=>30.25,2=>40.15],70.40))->assertSessionHasNoErrors();
    expect(\App\Services\InvoicePayments::summary(1,150)['balance_amount'])->toEqual(119.75);
    expect(\App\Services\InvoicePayments::summary(2,100)['balance_amount'])->toEqual(59.85);
});

test('legacy vouchers require explicit amount verification before counting toward settlement', function () {
    $this->post(route('baucar-bayaran.store'), receiptPayload('OLD',[1=>50],50))->assertSessionHasNoErrors();
    DB::table('payment_voucher_invoice')->update(['amount'=>null]);
    expect(\App\Services\InvoicePayments::summary(1,150))->toMatchArray(['paid_amount'=>0,'payment_status'=>'Perlu Semakan']);
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('Semak Bayaran Baucar Lama');
    $this->post(route('baucar-bayaran.store'), receiptPayload('NEW',[1=>20],20))->assertSessionHasErrors('invoice_ids');
    $this->post(route('baucar-bayaran.reconcile',1), ['allocations'=>[1=>60], 'total_amount'=>60])->assertSessionHasNoErrors();
    expect(\App\Services\InvoicePayments::summary(1,150)['balance_amount'])->toEqual(90);
    $this->post(route('baucar-bayaran.reconcile',1), ['allocations'=>[1=>60], 'total_amount'=>60])->assertStatus(422);
});


test('supplier voucher detail and deletion return to supplier list', function () {
    $this->post(route('baucar-bayaran.store'), receiptPayload('SUPPLIER-TEST', [1=>50], 50))->assertSessionHasNoErrors();
    DB::table('payment_voucher_documents')->where('id', 1)->update(['voucher_type' => 'supplier']);
    DB::table('payment_voucher_invoice')->delete();
    $this->get(route('baucar-bayaran.show', 1))->assertOk()->assertSee('Pembekal')->assertSee(route('baucar-bayaran.supplier'));
    $this->delete(route('baucar-bayaran.delete', 1))->assertRedirect(route('baucar-bayaran.supplier'));
});

test('voucher without invoice allows entering its actual amount', function () {
    $this->get(route('baucar-bayaran.form', ['tanpa_invoice' => 1]))->assertOk()->assertSee('Isi amaun baucar secara manual kerana tiada invoice dikaitkan.');
    $payload = receiptPayload('NO-INVOICE', [], 75);
    $this->post(route('baucar-bayaran.store'), $payload)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('payment_voucher_documents', ['voucher_no' => 'NO-INVOICE', 'total_amount' => 75, 'voucher_type' => 'customer']);
    $this->assertDatabaseCount('payment_voucher_invoice', 0);
});


test('supplier voucher saves edited items and rejects a mismatched total', function () {
    Schema::table('customer_supplier', function (Blueprint $t) {
        $t->integer('jenis_customer')->default(1); $t->text('address')->nullable(); $t->string('phone_no')->nullable(); $t->string('email')->nullable();
    });
    Schema::create('pembekal_quotation_master', function (Blueprint $t) {
        $t->increments('supplier_quotation_id'); $t->integer('supplier_id'); $t->string('company_name'); $t->string('quotation_no_supplier');
    });
    Schema::create('pembekal_quotation_item', function (Blueprint $t) {
        $t->increments('supplier_quotation_item_id'); $t->integer('supplier_quotation_id'); $t->text('item_description'); $t->integer('quantity'); $t->string('unit'); $t->decimal('unit_price',12,2); $t->decimal('subtotal',12,2);
    });
    DB::table('customer_supplier')->insert(['customer_id'=>2,'company_name'=>'Supplier','jenis_customer'=>2]);
    DB::table('pembekal_quotation_master')->insert(['supplier_quotation_id'=>1,'supplier_id'=>2,'company_name'=>'Supplier','quotation_no_supplier'=>'SQ-1']);
    DB::table('pembekal_quotation_item')->insert(['supplier_quotation_id'=>1,'item_description'=>'Original item','quantity'=>1,'unit'=>'unit','unit_price'=>100,'subtotal'=>100]);
    $payload=['supplier_quotation_id'=>1,'supplier_id'=>2,'voucher_no'=>'SUP-1','voucher_date'=>'2026-10-29','payment_method'=>'Cek','total_amount'=>50,'prepared_by'=>'Admin','prepared_role'=>'Manager','received_by'=>'Supplier','received_role'=>'Receiver','items_json'=>json_encode([['description'=>'Edited item','quantity'=>2,'unit'=>'unit','price'=>25]])];
    $bad=$payload; $bad['total_amount']=60;
    $this->post(route('baucar-bayaran.supplier.store'),$bad)->assertSessionHasErrors('total_amount');
    $this->assertDatabaseCount('payment_voucher_documents',0);
    $this->post(route('baucar-bayaran.supplier.store'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.supplier'));
    $this->assertDatabaseHas('payment_voucher_supplier_item',['item_description'=>'Edited item','quantity'=>2,'subtotal'=>50]);
    $this->assertDatabaseMissing('payment_voucher_supplier_item',['item_description'=>'Original item']);
    $this->assertDatabaseHas('payment_voucher_documents', ['voucher_no'=>'SUP-1', 'payment_method'=>'Cek', 'cheque_no'=>null]);
    $path=DB::table('payment_voucher_documents')->value('file_path');
    expect(Storage::disk('local')->get($path))->toStartWith('%PDF');
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('Edited item');
    $this->get(route('baucar-bayaran.supplier.edit',1))->assertOk()->assertSee('Edit Baucar Kepada Pembekal')->assertSee('name="supplier_quotation_id"', false)->assertViewHas('initialSupplierItems', fn ($items) => $items[0]['description'] === 'Edited item');
    $this->get(route('baucar-bayaran.supplier.form', ['supplier_quotation_id'=>1]))->assertOk()->assertDontSee('Tukar Pilihan');
    DB::table('customer_supplier')->insert(['customer_id'=>3,'company_name'=>'New Supplier','jenis_customer'=>2]);
    DB::table('pembekal_quotation_master')->insert(['supplier_quotation_id'=>2,'supplier_id'=>3,'company_name'=>'New Supplier','quotation_no_supplier'=>'SQ-2']);
    $payload['supplier_quotation_id']=2; $payload['supplier_id']=3;
    $payload['items_json']=json_encode([['description'=>'Replacement item','quantity'=>3,'unit'=>'unit','price'=>25]]);
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertSessionHasErrors('total_amount');
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $payload['total_amount']=75;
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.show',1));
    $this->assertDatabaseCount('payment_voucher_documents',1);
    $this->assertDatabaseCount('payment_voucher_supplier_item',1);
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'supplier_id'=>3,'supplier_quotation_id'=>2,'total_amount'=>75]);
    $this->assertDatabaseHas('payment_voucher_supplier_item',['item_description'=>'Replacement item','subtotal'=>75]);
    $newPath=DB::table('payment_voucher_documents')->value('file_path');
    expect($newPath)->not->toBe($path)->and(Storage::disk('local')->exists($path))->toBeFalse();
    expect(Storage::disk('local')->get($newPath))->toStartWith('%PDF');
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee(route('baucar-bayaran.supplier.preview',1))->assertDontSee('Muat Turun PDF');
    $this->get(route('baucar-bayaran.supplier.preview',1))->assertOk()->assertSee('Kembali ke Maklumat Baucar')->assertSee('Muat Turun PDF')->assertSee(route('baucar-bayaran.supplier.pdf',1))->assertSee('srcdoc=', false)->assertDontSee('download=1');
    $this->get(route('baucar-bayaran.file',['id'=>1,'download'=>1]))->assertOk()->assertDownload('SUP-1.pdf');
    $preview = $this->get(route('baucar-bayaran.supplier.preview',1))->viewData('previewHtml');
    expect($preview)->toContain('[ ] Tunai / Cash', '[ ] Pindahan Bank', '[ ] No Cek: ____________', '2px dotted');
    $pdfResponse = $this->get(route('baucar-bayaran.supplier.pdf',1))->assertOk()->assertHeader('Content-Type','application/pdf');
    expect($pdfResponse->headers->get('Content-Disposition'))->toStartWith('inline;');

    $this->get(route('baucar-bayaran.supplier.edit',1))->assertOk()->assertViewHas('quotation', fn ($q) => $q->supplier_id === 3);
    DB::table('payment_voucher_documents')->where('id',1)->update(['voucher_type'=>'customer']);
    $this->get(route('baucar-bayaran.supplier.edit',1))->assertNotFound();
    $this->get(route('baucar-bayaran.supplier.preview',1))->assertNotFound();
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertNotFound();

});
