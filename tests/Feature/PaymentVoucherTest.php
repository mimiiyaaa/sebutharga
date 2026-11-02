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
        '2026_10_26_100000_add_payment_allocations.php', '2026_10_28_120000_add_supplier_type_to_payment_voucher_documents_table.php', '2026_10_28_140000_link_supplier_payment_vouchers_to_quotations.php', '2026_10_29_160000_add_signature_fields_to_payment_voucher_documents_table.php', '2026_10_29_170000_create_payment_voucher_supplier_items_table.php', '2026_10_29_180000_add_cheque_number_to_payment_voucher_documents_table.php', '2026_11_01_110000_add_supplier_payment_status.php', '2026_11_01_120000_add_supplier_voucher_details.php'] as $migration) {
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



function supplierPaymentSetup(): void {
    Schema::table('customer_supplier', function (Blueprint $t) {
        $t->integer('jenis_customer')->default(1); $t->text('address')->nullable(); $t->string('phone_no')->nullable(); $t->string('email')->nullable(); $t->string('customer_name')->nullable();
    });
    Schema::create('companies', function (Blueprint $t) { $t->increments('company_id'); $t->string('nama_syarikat'); });
    (require database_path('migrations/2026_10_12_020000_create_supplier_documents.php'))->up();
    (require database_path('migrations/2026_11_01_100000_create_supplier_invoice_payments.php'))->up();
    DB::table('customer_supplier')->insert(['customer_id'=>2,'company_name'=>'Supplier','jenis_customer'=>2]);
    DB::table('customer_supplier')->insert(['customer_id'=>3,'company_name'=>'Other Supplier','jenis_customer'=>2]);
    foreach ([1=>2,2=>2,3=>3] as $id=>$supplier) DB::table('supplier_documents')->insert(['id'=>$id,'kind'=>'invoice','supplier_id'=>$supplier,'supplier_name'=>'Supplier','document_no'=>'SINV-'.$id,'document_date'=>'2026-10-29','source'=>'PDF','file_path'=>'test.pdf','file_name'=>'test.pdf','file_mime'=>'application/pdf','extraction_method'=>'manual','items'=>'[]','total_amount'=>100]);
}
function supplierPaymentPayload($number, $payments): array {
    return ['supplier_id'=>2,'voucher_no'=>$number,'voucher_date'=>'2026-11-01','payment_method'=>'Cek','supplier_payment_status'=>'paid',
        'issuer_name'=>'Issuer','issuer_address'=>'Issuer Address','issuer_phone'=>'12345','issuer_email'=>'issuer@example.test',
        'supplier_name'=>'Supplier Snapshot','supplier_address'=>'Supplier Address','supplier_phone'=>'67890','supplier_email'=>'supplier@example.test','supplier_person'=>'Person',
        'prepared_by'=>'Tester','prepared_role'=>'Manager','received_by'=>'Supplier','received_role'=>'Receiver','payments'=>$payments];
}

test('supplier invoice payment saves voucher references details and PDF together in one form', function () {
    supplierPaymentSetup();
    $this->get(route('baucar-bayaran.supplier.create',['invoice_id'=>1]))->assertOk()->assertSee('Invois Yang Dibayar')->assertDontSee('Sebut Harga Dipautkan')->assertViewHas('selectedSupplierId',2);
    $payload=supplierPaymentPayload('SPV-1',[['invoice_id'=>1,'amount'=>40],['invoice_id'=>2,'amount'=>25]]);
    $this->post(route('baucar-bayaran.supplier.store'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.show',1));
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'total_amount'=>65,'supplier_quotation_id'=>null,'supplier_payment_status'=>'paid']);
    $this->assertDatabaseHas('supplier_invoice_payments',['payment_voucher_id'=>1,'supplier_document_id'=>1,'amount'=>40]);
    $this->assertDatabaseHas('supplier_invoice_payments',['payment_voucher_id'=>1,'supplier_document_id'=>2,'amount'=>25]);
    $path=DB::table('payment_voucher_documents')->value('file_path');
    expect(Storage::disk('local')->get($path))->toStartWith('%PDF');
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('SINV-1')->assertSee('Supplier Address')->assertDontSee('Padankan');
    $this->get(route('baucar-bayaran.supplier'))->assertOk()->assertSee('SINV-1')->assertSee('Supplier');
    $this->get(route('baucar-bayaran.supplier.edit',1))->assertOk()->assertViewHas('referencesLocked',true)->assertViewHas('initialPayments',fn($p)=>count($p)===2 && $p[0]['amount']===40.0);
    $preview=$this->get(route('baucar-bayaran.supplier.preview',1))->assertOk()->viewData('previewHtml');
    expect($preview)->toContain('SINV-1')->toContain('SINV-2')->toContain('Issuer Address')->toContain('[ ] Tunai / Cash')->toContain('[ ] Pindahan Bank')->toContain('[ ] No Cek: ____________');
    $this->get(route('baucar-bayaran.supplier.pdf',1))->assertOk()->assertHeader('Content-Type','application/pdf');
    $this->get(route('monitoring.voucher',1))->assertRedirect(route('baucar-bayaran.supplier.edit',1));
    $payload['payments']=[['invoice_id'=>1,'amount'=>30],['invoice_id'=>2,'amount'=>80]]; $payload['supplier_payment_status']='unpaid'; $payload['issuer_name']='Updated Issuer';
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.show',1));
    $this->assertDatabaseCount('supplier_invoice_payments',2); $this->assertDatabaseCount('payment_voucher_supplier_item',2);
    $this->assertDatabaseHas('supplier_invoice_payments',['supplier_document_id'=>2,'amount'=>80]);
    Storage::disk('local')->assertMissing($path);
    $this->get(route('baucar-bayaran.show',1))->assertSee('Updated Issuer')->assertSee('SINV-2');
    $this->delete(route('baucar-bayaran.delete',1))->assertRedirect(route('baucar-bayaran.supplier'));
    $this->assertDatabaseCount('supplier_invoice_payments',0);
});

test('supplier invoice payment rejects missing foreign duplicate and excessive amounts without changing existing records', function () {
    supplierPaymentSetup();
    $payload=supplierPaymentPayload('SPV-1',[['invoice_id'=>1,'amount'=>60]]);
    $this->post(route('baucar-bayaran.supplier.store'),$payload)->assertSessionHasNoErrors();
    $path=DB::table('payment_voucher_documents')->value('file_path');
    foreach ([[],[['invoice_id'=>2,'amount'=>1]],[['invoice_id'=>3,'amount'=>1]],[['invoice_id'=>1,'amount'=>1],['invoice_id'=>1,'amount'=>2]],[['invoice_id'=>1,'amount'=>101]]] as $payments) {
        $bad=$payload; $bad['payments']=$payments;
        $this->put(route('baucar-bayaran.supplier.update',1),$bad)->assertSessionHasErrors();
        $this->assertDatabaseHas('supplier_invoice_payments',['supplier_document_id'=>1,'amount'=>60]);
        Storage::disk('local')->assertExists($path);
    }
    $bad=$payload; $bad['supplier_id']=3; $bad['payments']=[['invoice_id'=>3,'amount'=>20]];
    $this->put(route('baucar-bayaran.supplier.update',1),$bad)->assertSessionHasErrors('supplier_id');
    $payload['supplier_name']='Forged supplier name';
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'payee_name'=>'Supplier','supplier_id'=>2]);
    $next=supplierPaymentPayload('SPV-2',[['invoice_id'=>1,'amount'=>41]]);
    $this->post(route('baucar-bayaran.supplier.store'),$next)->assertSessionHasErrors('payments.0.amount');
    $next['payments'][0]['amount']=40;
    $this->post(route('baucar-bayaran.supplier.store'),$next)->assertSessionHasNoErrors();
    expect(DB::table('supplier_invoice_payments')->where('supplier_document_id',1)->sum('amount'))->toEqual(100);
    $this->get(route('baucar-bayaran.supplier.create',['invoice_id'=>999]))->assertNotFound();
    DB::table('payment_voucher_documents')->where('id',1)->update(['voucher_type'=>'customer']);
    $this->put(route('baucar-bayaran.supplier.update',1),$payload)->assertNotFound();
    auth()->logout();
    $this->post(route('baucar-bayaran.supplier.store'),$payload)->assertRedirect(route('login'));
});


test('supplier standalone voucher has a full page choice and saves without any invoice', function () {
    supplierPaymentSetup();
    $this->get(route('baucar-bayaran.supplier.create'))->assertOk()->assertSee('Baucar Standalone')->assertSee('Daripada Invois');
    $this->get(route('baucar-bayaran.supplier.form', ['source'=>'standalone']))->assertOk()->assertDontSee('Invois Yang Dibayar');
    $payload = supplierPaymentPayload('PV-STANDALONE', []);
    unset($payload['payments']);
    $payload += ['source_type'=>'standalone','total_amount'=>75.50,'description'=>'Upah angkut barang'];
    $payload['supplier_id'] = null;
    $payload['supplier_name'] = 'Ali';
    $payload['supplier_payment_status'] = 'unpaid';
    $this->post(route('baucar-bayaran.supplier.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('baucar-bayaran.show',1));
    $this->assertDatabaseHas('payment_voucher_documents', ['id'=>1,'supplier_id'=>null,'payee_name'=>'Ali','total_amount'=>75.50]);
    $this->assertDatabaseCount('supplier_invoice_payments',0);
    $this->get(route('baucar-bayaran.supplier'))->assertOk()->assertSee('Tanpa Invois');
    $this->get(route('baucar-bayaran.show',1))->assertOk()->assertSee('Baucar Standalone')->assertSee('Upah angkut barang');
    $this->get(route('baucar-bayaran.supplier.edit',1))->assertOk()->assertViewHas('standalone',true)->assertDontSee('Invois Yang Dibayar');
    $this->get(route('baucar-bayaran.supplier.preview',1))->assertOk()->assertViewHas('previewHtml',fn($html)=>str_contains($html,'Upah angkut barang'));
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'paid'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'supplier_payment_status'=>'paid']);
    $payload['total_amount']=80;
    $this->put(route('baucar-bayaran.supplier.update',1), $payload)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'total_amount'=>80]);
    $this->assertDatabaseCount('supplier_invoice_payments',0);
    $payload['total_amount']=0;
    $this->put(route('baucar-bayaran.supplier.update',1), $payload)->assertSessionHasErrors('total_amount');
    $payload['total_amount']=80; $payload['payments']=[['invoice_id'=>1,'amount'=>80]];
    $this->put(route('baucar-bayaran.supplier.update',1), $payload)->assertSessionHasErrors('payments');
});
