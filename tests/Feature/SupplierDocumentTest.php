<?php

use App\Models\User;
use App\Services\SupplierQuotationOcr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array']);
    DB::purge('sqlite'); Storage::fake('local');
    Schema::create('users', function (Blueprint $table) {
        $table->increments('id'); $table->string('name'); $table->string('email')->nullable(); $table->string('jawatan')->nullable();
    });
    DB::table('users')->insert(['id'=>42,'name'=>'Pencipta Asal','email'=>'creator@example.test','jawatan'=>'Pengurus']);
    Schema::create('customer_supplier',function(Blueprint $table){$table->increments('customer_id');$table->integer('jenis_customer');$table->string('company_name');});
    Schema::create('lo_inden_master', function (Blueprint $table) {
        $table->increments('lo_inden_id'); $table->string('lo_inden_no'); $table->integer('customer_id');
    });
    DB::table('lo_inden_master')->insert(['lo_inden_no'=>'LO-EDIT','customer_id'=>2]);
    (require database_path('migrations/2026_10_12_020000_create_supplier_documents.php'))->up();
    Schema::table('supplier_documents', fn (Blueprint $table) => $table->string('document_title')->nullable());
    DB::table('customer_supplier')->insert([['customer_id'=>1,'jenis_customer'=>2,'company_name'=>'K ONE RESOURCES'],['customer_id'=>2,'jenis_customer'=>1,'company_name'=>'Customer']]);
    $user=new User();$user->id=42;$user->name='Tester';$this->actingAs($user);
    $this->supplierData=['supplier_id'=>1,'document_no'=>'SUP-001','document_date'=>'2026-08-11','source'=>'WhatsApp','source_details'=>'Dihantar oleh pembekal','reviewed'=>'1','total_amount'=>106,
        'items'=>[['description'=>'Laptop','quantity'=>2,'unit'=>'Unit','price'=>50]]];
});

function supplierTestPdf() {return UploadedFile::fake()->createWithContent('supplier.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");}

test('supplier upload save list view file and delete work for both document types',function(string $prefix){
    $this->get(route($prefix))->assertOk()->assertSee(route($prefix.'.create'),false);
    $form=$this->get(route($prefix.'.create'))->assertOk()->assertSee('Sumber Dokumen')->assertSee('document_file');
    if(getenv('SUPPLIER_UI_CHECK'))file_put_contents(storage_path('app/supplier-form-check.html'),$form->getContent());
    $this->post(route($prefix.'.store'),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors()->assertRedirect(route($prefix));
    $record=DB::table('supplier_documents')->first();
    expect($record->source)->toBe('WhatsApp')->and($record->source_details)->toBe('Dihantar oleh pembekal');
    if($prefix==='delivery-order.supplier')expect($record->total_amount)->toBeNull();
    Storage::disk('local')->assertExists($record->file_path);
    $this->get(route($prefix))->assertOk()->assertSee('SUP-001')->assertSee('WhatsApp')->assertSee('Lihat')->assertSee('Padam');
    $this->get(route($prefix.'.show',$record->id))->assertOk()->assertSee('Laptop')->assertSee('supplier.pdf');
    $this->get(route($prefix.'.file',$record->id))->assertOk()->assertHeader('content-type','application/pdf');
    $this->get(route($prefix.'.file',['id'=>$record->id,'download'=>1]))->assertDownload('supplier.pdf');
    $this->delete(route($prefix.'.delete',$record->id))->assertRedirect(route($prefix));
    $this->assertDatabaseCount('supplier_documents',0);Storage::disk('local')->assertMissing($record->file_path);
})->with(['invoice.supplier','delivery-order.supplier']);

test('supplier extraction uses OCR and returns reviewable values with provenance',function(){
    $this->mock(SupplierQuotationOcr::class,function($mock){$mock->shouldReceive('extractText')->once()->andReturn(['method'=>'teks PDF','text'=>"K ONE RESOURCES INVOIS\nNo. Dokumen: INV-001\nTarikh: 11 Ogos 2026\nBIL KETERANGAN KUANTITI UNIT HARGA JUMLAH\n1 Laptop 2 Unit 50.00 100.00\nJUMLAH BESAR (RM): 106.00"]);});
    $file=supplierTestPdf();
    $this->postJson(route('invoice.supplier.extract'),['document_file'=>$file])->assertOk()->assertJsonPath('data.document_no','INV-001')->assertJsonPath('data.document_date','2026-08-11')->assertJsonPath('data.supplier_id',1)->assertJsonPath('data.items.0.quantity',2)->assertJsonPath('data.total_amount',106);
    $this->post(route('invoice.supplier.store'),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('supplier_documents',['extraction_method'=>'teks PDF']);
});

test('supplier validation rejects invalid files customers duplicates and missing review',function(){
    $this->postJson(route('invoice.supplier.extract'),['document_file'=>UploadedFile::fake()->create('bad.txt',1,'text/plain')])->assertUnprocessable()->assertJsonValidationErrors('document_file');
    foreach(['supplier_id'=>2,'reviewed'=>0,'source'=>'unknown','items.0.quantity'=>-1,'total_amount'=>-1] as $key=>$value){$data=$this->supplierData;data_set($data,$key,$value);$this->post(route('invoice.supplier.store'),$data+['document_file'=>supplierTestPdf()])->assertSessionHasErrors($key);}
    $this->assertDatabaseCount('supplier_documents',0);
    $this->post(route('invoice.supplier.store'),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors();
    $this->post(route('invoice.supplier.store'),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasErrors('document_no');
    $this->get(route('delivery-order.supplier.show',1))->assertNotFound();
    $this->get(route('delivery-order.supplier.file',1))->assertNotFound();
    $this->delete(route('delivery-order.supplier.delete',1))->assertNotFound();
    $this->assertDatabaseCount('supplier_documents',1);
    expect(Storage::disk('local')->allFiles())->toHaveCount(1);
});

test('supplier OCR failure can be corrected manually and all routes require login',function(){
    $this->mock(SupplierQuotationOcr::class,function($mock){$mock->shouldReceive('extractText')->once()->andThrow(new RuntimeException('OCR unavailable'));});
    $this->postJson(route('invoice.supplier.extract'),['document_file'=>supplierTestPdf()])->assertUnprocessable()->assertJsonPath('message','OCR unavailable');
    $this->post(route('invoice.supplier.store'),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('supplier_documents',['extraction_method'=>'Semakan manual']);
    auth()->forgetGuards();
    $this->get(route('invoice.supplier.file',1))->assertRedirect(route('login'));
    $this->postJson(route('invoice.supplier.extract'),['document_file'=>supplierTestPdf()])->assertUnauthorized();
    $this->delete(route('invoice.supplier.delete',1))->assertRedirect(route('login'));
});

test('supplier documents can be edited without reuploading or losing creator information', function (string $prefix) {
    $this->post(route($prefix.'.store'), $this->supplierData + ['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors();
    $original = DB::table('supplier_documents')->first();
    $this->get(route($prefix.'.show', $original->id))->assertOk()->assertSee(route($prefix.'.edit', $original->id), false);
    $this->get(route($prefix.'.edit', $original->id))->assertOk()
        ->assertSee(route($prefix.'.update', $original->id), false)
        ->assertViewHas('document', fn ($document) => $document->file_path === $original->file_path)
        ->assertSee('value="1" selected', false)->assertSee('value="WhatsApp" selected', false)
        ->assertSee('Dihantar oleh pembekal')->assertSee('supplier.pdf');
    $editor = new User(); $editor->id=99; $editor->name='Editor'; $this->actingAs($editor);
    $data=$this->supplierData;
    $data['items'][0]['description']='Updated laptop'; $data['items'][0]['quantity']=3;
    $data['notes']='Updated notes'; $data['lo_reference']='LO-EDIT'; $data['source']='';
    $this->put(route($prefix.'.update', $original->id), $data)->assertSessionHasNoErrors()->assertRedirect(route($prefix.'.show',$original->id));
    $updated=DB::table('supplier_documents')->first();
    expect($updated->file_path)->toBe($original->file_path)
        ->and($updated->created_by)->toBe(42)->and($updated->created_at)->toBe($original->created_at)
        ->and($updated->source)->toBe('WhatsApp')->and($updated->po_reference)->toBe('LO-EDIT')
        ->and(json_decode($updated->items,true)[0]['description'])->toBe('Updated laptop');
    $this->assertDatabaseCount('supplier_documents',1);
    Storage::disk('local')->assertExists($original->file_path);
    $this->get(route($prefix.'.show',$original->id))->assertOk()->assertSee('Updated laptop')->assertSee('Updated notes')->assertSee('No. Rujukan LO')->assertSee('LO-EDIT')->assertDontSee('No. PO');
    $this->get(route($prefix.'.edit',$original->id))->assertOk()->assertSee('name="lo_reference"',false)->assertSee('value="LO-EDIT"',false)->assertDontSee('No. Rujukan PO');
})->with(['invoice.supplier','delivery-order.supplier']);

test('supplier document replacement validates first and cleans up only the replaced file', function (string $prefix) {
    $this->post(route($prefix.'.store'), $this->supplierData + ['document_file'=>supplierTestPdf()]);
    $original=DB::table('supplier_documents')->first();
    $invalid=$this->supplierData; $invalid['items'][0]['quantity']=0;
    $this->put(route($prefix.'.update',$original->id), $invalid+['document_file'=>supplierTestPdf()])->assertSessionHasErrors('items.0.quantity');
    Storage::disk('local')->assertExists($original->file_path);
    expect(Storage::disk('local')->allFiles())->toHaveCount(1);
    $this->put(route($prefix.'.update',$original->id),$this->supplierData+['document_file'=>supplierTestPdf()])->assertSessionHasNoErrors();
    $updated=DB::table('supplier_documents')->first();
    expect($updated->file_path)->not->toBe($original->file_path);
    Storage::disk('local')->assertMissing($original->file_path);
    Storage::disk('local')->assertExists($updated->file_path);
})->with(['invoice.supplier','delivery-order.supplier']);

test('supplier edit rejects duplicate numbers wrong document types and unauthenticated requests', function () {
    $this->post(route('invoice.supplier.store'),$this->supplierData+['document_file'=>supplierTestPdf()]);
    $second=$this->supplierData; $second['document_no']='SUP-002';
    $this->post(route('invoice.supplier.store'),$second+['document_file'=>supplierTestPdf()]);
    $this->put(route('invoice.supplier.update',2),$this->supplierData)->assertSessionHasErrors('document_no');
    $this->assertDatabaseHas('supplier_documents',['id'=>2,'document_no'=>'SUP-002']);
    $this->get(route('delivery-order.supplier.edit',1))->assertNotFound();
    $this->put(route('delivery-order.supplier.update',1),$this->supplierData)->assertNotFound();
    auth()->forgetGuards();
    $this->get(route('invoice.supplier.edit',1))->assertRedirect(route('login'));
    $this->put(route('invoice.supplier.update',1),$this->supplierData)->assertRedirect(route('login'));
});

test('supplier LO dropdown saves valid choices and preserves legacy references', function (string $prefix) {
    $this->get(route($prefix.'.create'))->assertOk()->assertSee('<select id="lo_reference"',false)->assertSee('LO-EDIT');
    $data=$this->supplierData+['lo_reference'=>'LO-EDIT','document_file'=>supplierTestPdf()];
    $this->post(route($prefix.'.store'),$data)->assertSessionHasNoErrors();
    $record=DB::table('supplier_documents')->first();
    $this->get(route($prefix.'.edit',$record->id))->assertOk()->assertSee('value="LO-EDIT" selected',false);
    $update=$this->supplierData+['lo_reference'=>'INVALID-LO'];
    $this->put(route($prefix.'.update',$record->id),$update)->assertSessionHasErrors('lo_reference');
    $this->assertDatabaseHas('supplier_documents',['id'=>$record->id,'po_reference'=>'LO-EDIT']);
    session()->forget('_old_input');
    DB::table('supplier_documents')->where('id',$record->id)->update(['po_reference'=>'LEGACY-LO']);
    $this->get(route($prefix.'.edit',$record->id))->assertOk()->assertSee('value="LEGACY-LO" selected',false);
    $update['lo_reference']='LEGACY-LO';
    $this->put(route($prefix.'.update',$record->id),$update)->assertSessionHasNoErrors();
    $update['lo_reference']='LO-EDIT';
    $this->put(route($prefix.'.update',$record->id),$update)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('supplier_documents',['id'=>$record->id,'po_reference'=>'LO-EDIT']);
})->with(['invoice.supplier','delivery-order.supplier']);


test('supplier lists render matching headers and empty states without a quantity column', function (string $prefix) {
    $response=$this->get(route($prefix))->assertOk()->assertDontSee('Jumlah Kuantiti');
    $response->assertSee('colspan="'.($prefix === 'invoice.supplier' ? 7 : 5).'"',false);
    if ($prefix === 'invoice.supplier') $response->assertSee('Jumlah (RM)');
})->with(['invoice.supplier','delivery-order.supplier']);
