<?php

use App\Models\User;
use App\Services\DashboardData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:', 'session.driver'=>'array']);
    DB::purge('sqlite');
    Schema::create('customer_supplier', function (Blueprint $t) { $t->increments('customer_id'); $t->string('company_name'); });
    Schema::create('invoice_master', function (Blueprint $t) {
        $t->increments('invoice_id'); $t->integer('customer_id'); $t->string('invoice_no'); $t->string('document_kind');
        $t->date('invoice_date'); $t->date('due_date')->nullable(); $t->decimal('total_amount',12,2); $t->text('document_data')->nullable();
    });
    Schema::create('payment_voucher_documents', function (Blueprint $t) { $t->increments('id'); $t->string('voucher_no'); $t->date('voucher_date'); $t->string('voucher_type')->default('customer'); $t->integer('supplier_id')->nullable(); $t->string('payee_name')->default('Supplier'); $t->text('supplier_details')->nullable(); $t->decimal('total_amount',12,2)->default(0); });
    Schema::create('payment_voucher_invoice', function (Blueprint $t) { $t->increments('id'); $t->integer('invoice_id'); $t->integer('payment_voucher_id'); $t->decimal('amount',12,2)->nullable(); });
    Schema::create('lo_inden_master', function (Blueprint $t) { $t->increments('lo_inden_id'); $t->string('lo_inden_no'); $t->integer('customer_id'); $t->integer('quotation_id')->nullable(); $t->integer('quotation_detail_id')->nullable(); $t->date('lo_inden_date'); $t->decimal('amount',12,2)->default(0); });
    Schema::create('purchase_order_master', function (Blueprint $t) { $t->increments('purchase_order_id'); $t->string('po_no'); $t->integer('customer_id')->nullable(); $t->date('po_date'); $t->decimal('net_amount',12,2)->default(0); $t->integer('lo_inden_id')->nullable(); });
    Schema::create('purchase_order_item', function (Blueprint $t) { $t->id(); $t->integer('purchase_order_id'); $t->text('item_description'); });
    Schema::create('sebutharga_master', function (Blueprint $t) { $t->increments('quotation_id'); $t->integer('quotation_detail_id')->nullable(); $t->string('quotation_no'); $t->date('quotation_date')->nullable(); });
    Schema::create('sebutharga_detail', function (Blueprint $t) { $t->increments('quotation_detail_id'); $t->integer('quotation_id'); $t->date('quotation_date'); $t->string('quotation_title')->nullable(); $t->string('status_draft'); $t->timestamp('sent_at')->nullable(); $t->integer('status_quotation')->nullable(); });
    Schema::create('delivery_order_master', function (Blueprint $t) { $t->increments('delivery_order_id'); $t->string('do_no')->default('DO-1'); $t->integer('customer_id'); $t->string('document_kind'); $t->date('do_date'); $t->text('document_data')->nullable(); });
    Schema::create('supplier_documents', function (Blueprint $t) {
        $t->id(); $t->string('kind'); $t->integer('supplier_id'); $t->string('supplier_name');
        $t->string('document_no'); $t->date('document_date'); $t->decimal('total_amount',12,2)->nullable();
    });
    (require database_path('migrations/2026_11_01_100000_create_supplier_invoice_payments.php'))->up();
    (require database_path('migrations/2026_11_01_110000_add_supplier_payment_status.php'))->up();
    Schema::table('payment_voucher_documents', fn (Blueprint $t) => $t->timestamp('updated_at')->nullable());
    $user = new User(); $user->id = 42; $user->name = 'Tester'; $this->actingAs($user);
});

test('payment notifications show outstanding customer invoices and pending reviews only', function () {
    dashboardInvoice(1, '2020-01-01', 1000, 'customer', json_encode(['customer_name' => 'Customer Snapshot']));
    dashboardInvoice(2, '2020-01-01', 100);
    dashboardInvoice(3, '2020-01-01', 100, 'supplier');
    dashboardInvoice(4, '2099-01-01', 100);
    dashboardInvoice(5, '2020-01-01', 100);
    dashboardReceipt(1, 1, '2020-01-02', 250);
    dashboardReceipt(2, 2, '2020-01-02', 100);
    dashboardReceipt(3, 5, '2020-01-02', null);
    dashboardReceipt(4, 1, '2099-01-02', 750);
    $view = (new \App\View\Components\header\NotificationDropdown())->render();
    $data = $view->getData();
    expect($data['count'])->toBe(2)
        ->and($data['notifications']->pluck('invoice_id')->all())->toBe([1, 5])
        ->and($data['notifications'][0]->balance)->toEqual(750)
        ->and($data['notifications'][0]->customer)->toBe('Customer Snapshot')
        ->and($data['notifications'][0]->status)->toBe('Bayaran Sebahagian')
        ->and($data['notifications'][1]->status)->toBe('Perlu Semakan');
    expect($view->render())->toContain(route('invoice.customer.payments', 1))->not->toContain('Terry Franci');
});

test('monitoring has its own authenticated page with real data and leaves dashboard', function () {
    $this->get(route('monitoring'))->assertOk()->assertSee('Pemantauan Bayaran dan Reminder')->assertDontSee('Data contoh')->assertSee('Tiada invois untuk pilihan ini.');
    $this->get(route('dashboard'))->assertOk()->assertDontSee('Pemantauan Bayaran dan Reminder');
    auth()->logout();
    $this->get(route('monitoring'))->assertRedirect(route('login'));
});

test('monitoring uses due dates verified payments and customer snapshots', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-27 14:35:00', 'Asia/Kuala_Lumpur'));
    foreach ([1, 2, 3, 4, 5, 6] as $id) dashboardInvoice($id, '2026-10-01', 100, 'customer', json_encode(['customer_name' => 'Snapshot Customer']));
    dashboardInvoice(7, '2026-10-01', 100, 'supplier');
    dashboardInvoice(8, '2026-11-01', 100);
    DB::table('invoice_master')->where('invoice_id', 1)->update(['due_date' => '2026-10-26']);
    DB::table('invoice_master')->where('invoice_id', 2)->update(['due_date' => '2026-11-03']);
    DB::table('invoice_master')->where('invoice_id', 3)->update(['due_date' => '2026-11-04']);
    dashboardReceipt(1, 1, '2026-10-25', 25);
    dashboardReceipt(2, 4, '2026-10-25', 100);
    dashboardReceipt(3, 5, '2026-10-25', null);
    dashboardReceipt(4, 6, '2026-11-01', 100);
    $response = $this->get(route('monitoring'))->assertOk()->assertSee('Snapshot Customer')->assertSee('27/10/2026 14:35');
    $data = $response->viewData('monitoring');
    expect($data['summary'])->toBe(['unpaid' => 2, 'unpaid_total' => 200, 'overdue' => 1, 'overdue_total' => 75, 'upcoming' => 1, 'upcoming_total' => 100, 'review' => 1, 'review_total' => 100, 'paid' => 1, 'paid_total' => 100])
        ->and($data['records']->firstWhere('invoice_id', 1)['balance'])->toEqual(75)
        ->and($data['records']->pluck('invoice_id')->all())->not->toContain(4, 7, 8);
    $this->get(route('monitoring', ['priority' => 'Sudah Lewat']))->assertOk()
        ->assertViewHas('monitoring', fn ($data) => $data['records']->count() === 1);
    $this->travelBack();
});

function dashboardInvoice($id, $date, $total, $kind = 'customer', $data = null) {
    DB::table('invoice_master')->insert(['invoice_id'=>$id, 'invoice_no'=>'INV-'.$id, 'customer_id'=>1, 'invoice_date'=>$date, 'total_amount'=>$total, 'document_kind'=>$kind, 'document_data'=>$data]);
}
function dashboardReceipt($id, $invoice, $date, $amount) {
    DB::table('payment_voucher_documents')->insert(['id'=>$id, 'voucher_no'=>'PV-'.$id, 'voucher_date'=>$date]);
    DB::table('payment_voucher_invoice')->insert(['payment_voucher_id'=>$id, 'invoice_id'=>$invoice, 'amount'=>$amount]);
}

test('dashboard distinguishes period receipts from cumulative balances and excludes future payments', function () {
    dashboardInvoice(1, '2026-06-01', 1000);
    dashboardInvoice(2, '2026-10-01', 500);
    dashboardInvoice(3, '2026-11-01', 900);
    dashboardInvoice(4, '2026-10-01', 700, 'supplier');
    dashboardReceipt(1,1,'2026-09-01',200);
    dashboardReceipt(2,1,'2026-10-10',300);
    dashboardReceipt(3,1,'2026-11-10',500);
    dashboardReceipt(4,2,'2026-10-10',null);
    dashboardReceipt(5,4,'2026-10-10',700);
    $data = (new DashboardData())->build(CarbonImmutable::parse('2026-10-01'),CarbonImmutable::parse('2026-10-27'));
    expect($data['financial'][0][1])->toEqual(1500)
        ->and($data['financial'][1][1])->toEqual(500)
        ->and($data['financial'][2][1])->toEqual(1000)
        ->and($data['invoiced'])->toBe([0.5])
        ->and($data['received'])->toBe([0.3])
        ->and($data['statuses']['Perlu Semakan'])->toBe(1)
        ->and($data['pendingCount'])->toBe(1);
    $this->get(route('dashboard',['start'=>'2026-10-01','end'=>'2026-10-27']))->assertOk()->assertSee('INV-1')->assertDontSee('INV-CONTOH')->assertSee('1,000.00');
    $this->get(route('dashboard',['start'=>'2026-10-01','end'=>'2026-10-27','panel'=>'review']))->assertRedirect(route('baucar-bayaran.index'));
});

test('aging boundaries and paid invoices use actual allocation amounts', function () {
    $end = CarbonImmutable::parse('2026-10-27');
    foreach ([30,31,60,61,90,91] as $index => $days) dashboardInvoice($index+1,$end->subDays($days)->toDateString(),100);
    dashboardReceipt(1,1,'2026-10-27',100);
    $data=(new DashboardData())->build($end->startOfMonth(),$end);
    expect($data['aging']->pluck(1)->all())->toEqual([0,200,200,100])
        ->and($data['statuses']['Selesai Dibayar'])->toBe(1);
    $this->get(route('dashboard',['start'=>'2026-10-01','end'=>'2026-10-27','age'=>3]))->assertOk()->assertViewHas('balances', fn ($rows) => $rows->total() === 1 && $rows->first()['id'] === 6);
});

test('document counts use one active version and LO links rather than duplicate versions', function () {
    DB::table('sebutharga_master')->insert(['quotation_id'=>1,'quotation_detail_id'=>2,'quotation_no'=>'QT-1']);
    foreach ([1,2] as $id) DB::table('sebutharga_detail')->insert(['quotation_detail_id'=>$id,'quotation_id'=>1,'quotation_date'=>'2026-10-01','quotation_title'=>'Equipment','status_draft'=>'Final','sent_at'=>'2026-10-02']);
    $end=CarbonImmutable::parse('2026-10-27');
    $build=fn () => (new DashboardData())->build($end->startOfMonth(),$end);
    expect($build()['groups']['finals'])->toHaveCount(1);
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>1,'lo_inden_no'=>'LO-1','customer_id'=>1,'quotation_id'=>1,'quotation_detail_id'=>1,'lo_inden_date'=>'2026-10-03']);
    dashboardInvoice(1,'2026-10-04',100,'customer',json_encode(['lo_reference'=>'LO-1']));
    DB::table('delivery_order_master')->insert(['customer_id'=>1,'document_kind'=>'customer','do_date'=>'2026-10-04','document_data'=>json_encode(['lo_inden_id'=>1])]);
    expect($build()['groups']['finals'])->toHaveCount(0)
        ->and($build()['groups']['missing-invoice'])->toHaveCount(0)
        ->and($build()['groups']['missing-do'])->toHaveCount(0);
});

test('dashboard empty states date validation and authentication work', function () {
    $this->get(route('dashboard'))->assertOk()->assertSee('Tiada invois untuk pilihan ini.');
    $this->get(route('dashboard',['start'=>'2026-10-27','end'=>'2026-10-01']))->assertSessionHasErrors('start');
    $this->get(route('dashboard',['start'=>'2020-01-01','end'=>'2026-10-01']))->assertSessionHasErrors('start');
    $this->get(route('dashboard',['panel'=>'invalid']))->assertSessionHasErrors('panel');
    auth()->logout();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});


test('dashboard document cards navigate to sidebar pages without an inline records panel', function () {
    $response = $this->get(route('dashboard'))->assertOk()->assertDontSee('dashboard-records')->assertDontSee('panel=');
    foreach (['drafts' => 'sebut-harga', 'finals' => 'sebut-harga.final', 'lo' => 'lo', 'po' => 'purchase-order.index'] as $panel => $destination) {
        if ($panel !== 'drafts') $response->assertSee('href="'.route($destination).'"', false);
        $this->get(route('dashboard', ['panel' => $panel]))->assertRedirect(route($destination));
    }
});


test('monitoring separates partial payments from due priority and includes settled invoices', function () {
    dashboardInvoice(1, '2020-01-01', 1000);
    dashboardInvoice(2, '2020-01-01', 100);
    dashboardReceipt(1, 1, '2020-01-02', 250);
    dashboardReceipt(2, 2, '2020-01-02', 100);
    $response = $this->get(route('monitoring'))->assertOk()->assertSee('Bayaran Sebahagian')->assertSee('INV-2');
    expect($response->viewData('customerTotals'))->toEqual(['total'=>1100, 'paid'=>350, 'balance'=>750]);
    $this->get(route('monitoring', ['priority'=>'Belum Dibayar']))->assertViewHas('customerRecords', fn ($r) => $r->isEmpty());
    $this->get(route('monitoring', ['priority'=>'Bayaran Sebahagian']))->assertViewHas('customerRecords', fn ($r) => $r->count() === 1);
    $this->get(route('monitoring', ['from'=>'invalid']))->assertSessionHasErrors('from');
});

test('LO monitoring uses explicit links and shows missing documents without guessing by customer', function () {
    DB::table('customer_supplier')->insert(['customer_id'=>1,'company_name'=>'Customer']);
    foreach ([1,2] as $id) DB::table('lo_inden_master')->insert(['lo_inden_id'=>$id,'lo_inden_no'=>'LO-'.$id,'customer_id'=>1,'lo_inden_date'=>'2020-01-01']);
    DB::table('purchase_order_master')->insert(['purchase_order_id'=>1,'po_no'=>'PO-1','po_date'=>'2020-01-01']);
    dashboardInvoice(1, '2020-01-01', 100, 'customer', json_encode(['lo_inden_id'=>1,'lo_reference'=>'LO-2']));
    DB::table('delivery_order_master')->insert(['customer_id'=>1,'document_kind'=>'customer','do_date'=>'2020-01-01','document_data'=>json_encode(['lo_reference'=>'LO-1'])]);
    $this->get(route('monitoring.lo',1))->assertOk()->assertSee('PO-1');
    $this->put(route('monitoring.lo.save',1), ['orders'=>[1]])->assertRedirect(route('monitoring',['tab'=>'lo']));
    $response=$this->get(route('monitoring',['tab'=>'lo']))->assertOk()->assertSee('PO-1');
    expect($response->viewData('loRecords')->firstWhere('lo_inden_id',1)->complete)->toBeTrue()
        ->and($response->viewData('loRecords')->firstWhere('lo_inden_id',2)->invoices)->toBeEmpty();
    $this->put(route('monitoring.lo.save',2), ['orders'=>[1]])->assertSessionHasErrors('orders');
    $this->put(route('monitoring.lo.save',1), [])->assertRedirect();
    expect(DB::table('purchase_order_master')->value('lo_inden_id'))->toBeNull();
});

test('supplier voucher status controls invoice monitoring and can be reversed', function () {
    DB::table('supplier_documents')->insert(['id'=>1,'kind'=>'invoice','supplier_id'=>10,'supplier_name'=>'Supplier','document_no'=>'SUP-STATUS','document_date'=>'2020-01-01','total_amount'=>100]);
    DB::table('payment_voucher_documents')->insert(['id'=>1,'voucher_no'=>'PV-STATUS','voucher_date'=>'2020-01-02','voucher_type'=>'supplier','supplier_id'=>10,'total_amount'=>100]);
    DB::table('supplier_invoice_payments')->insert(['payment_voucher_id'=>1,'supplier_document_id'=>1,'amount'=>100]);
    $this->get(route('monitoring',['tab'=>'supplier']))->assertOk()->assertViewHas('supplierRecords',fn ($r) => $r->first()['balance']===100 && $r->first()['status']==='Belum Dibayar');
    $totals = fn () => (new DashboardData())->build(CarbonImmutable::parse('2020-01-01'), CarbonImmutable::parse('2020-01-31'))['supplierTotals'];
    expect($totals())->toEqual(['total'=>100, 'paid'=>0, 'balance'=>100]);
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'paid'])->assertRedirect(route('baucar-bayaran.show',1));
    $this->assertDatabaseHas('payment_voucher_documents',['id'=>1,'supplier_payment_status'=>'paid','supplier_status_changed_by'=>42]);
    $this->get(route('monitoring',['tab'=>'supplier']))->assertViewHas('supplierRecords',fn ($r) => $r->first()['balance']===0 && $r->first()['status']==='Selesai Dibayar');
    expect($totals())->toEqual(['total'=>100, 'paid'=>100, 'balance'=>0]);
    DB::table('supplier_invoice_payments')->where('payment_voucher_id',1)->update(['amount'=>40]);
    DB::table('payment_voucher_documents')->where('id',1)->update(['total_amount'=>40]);
    $this->get(route('monitoring',['tab'=>'supplier']))->assertViewHas('supplierRecords', fn ($rows) => $rows->first()['status'] === 'Bayaran Sebahagian' && $rows->first()['balance'] === 60);
    expect($totals())->toEqual(['total'=>100, 'paid'=>40, 'balance'=>60]);
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'unpaid'])->assertRedirect();
    $this->get(route('monitoring',['tab'=>'supplier']))->assertViewHas('supplierRecords',fn ($r) => $r->first()['paid']===0 && $r->first()['balance']===100);
    expect($totals())->toEqual(['total'=>100, 'paid'=>0, 'balance'=>100]);
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'invalid'])->assertSessionHasErrors('supplier_payment_status');
    DB::table('payment_voucher_documents')->where('id',1)->update(['voucher_type'=>'customer']);
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'paid'])->assertNotFound();
    auth()->logout();
    $this->put(route('monitoring.voucher.status',1),['supplier_payment_status'=>'paid'])->assertRedirect(route('login'));
});


test('supplier monitoring shows voucher existence independently of actual payments', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-01'));
    foreach ([1, 2] as $id) DB::table('supplier_documents')->insert(['id'=>$id,'kind'=>'invoice','supplier_id'=>10,'supplier_name'=>'Supplier','document_no'=>'SUP-'.$id,'document_date'=>'2026-10-01','total_amount'=>100]);
    foreach ([1 => ['paid', '2026-10-02'], 2 => ['unpaid', '2026-10-03'], 3 => ['paid', '2026-12-01']] as $id => [$status, $date]) {
        DB::table('payment_voucher_documents')->insert(['id'=>$id,'voucher_no'=>'PV-RECORD-'.$id,'voucher_date'=>$date,'voucher_type'=>'supplier','supplier_id'=>10,'total_amount'=>20,'supplier_payment_status'=>$status]);
        DB::table('supplier_invoice_payments')->insert(['payment_voucher_id'=>$id,'supplier_document_id'=>1,'amount'=>20]);
    }
    $response = $this->get(route('monitoring',['tab'=>'supplier']))->assertOk()->assertSee('Sudah Ada Baucar')->assertSee('Belum Ada Baucar')->assertSee(route('monitoring.invoice.vouchers', 1), false);
    $rows = $response->viewData('supplierRecords');
    expect($rows->firstWhere('id', 1)['linked_vouchers'])->toHaveCount(3)
        ->and($rows->firstWhere('id', 1)['paid'])->toEqual(20)
        ->and($rows->firstWhere('id', 1)['balance'])->toEqual(80)
        ->and($rows->firstWhere('id', 2)['linked_vouchers'])->toBeEmpty();
    $this->get(route('monitoring',['tab'=>'customer']))->assertOk()->assertDontSee('Sudah Ada Baucar');
    $this->travelBack();
});


test('standalone vouchers are complete records and never settle supplier invoices', function () {
    DB::table('supplier_documents')->insert(['id'=>1,'kind'=>'invoice','supplier_id'=>10,'supplier_name'=>'Supplier','document_no'=>'SUP-1','document_date'=>'2020-01-01','total_amount'=>100]);
    DB::table('payment_voucher_documents')->insert(['id'=>1,'voucher_no'=>'PV-STANDALONE','voucher_date'=>'2020-01-02','voucher_type'=>'supplier','supplier_id'=>10,'total_amount'=>100,'supplier_payment_status'=>'paid','supplier_details'=>json_encode(['source_type'=>'standalone'])]);
    $response=$this->get(route('monitoring',['tab'=>'supplier']))->assertOk()->assertSee('Baucar Standalone');
    expect($response->viewData('supplierRecords')->first()['paid'])->toEqual(0)
        ->and($response->viewData('supplierRecords')->first()['balance'])->toEqual(100)
        ->and($response->viewData('vouchers')->first()->unallocated)->toEqual(0);
});

test('monitoring filters voucher types without filtering invoice records', function () {
    foreach ([1 => 'standalone', 2 => 'invoice'] as $id => $source) {
        DB::table('payment_voucher_documents')->insert(['id'=>$id,'voucher_no'=>'PV-FILTER-'.$id,'voucher_date'=>'2020-01-02','voucher_type'=>'supplier','total_amount'=>$id * 100,'supplier_payment_status'=>'paid','supplier_details'=>json_encode(['source_type'=>$source])]);
    }
    $this->get(route('monitoring',['tab'=>'supplier']))->assertOk()->assertViewHas('vouchers', fn ($rows) => $rows->count() === 2);
    foreach (['standalone'=>1, 'invoice'=>2] as $source => $id) {
        $this->get(route('monitoring',['tab'=>'supplier','voucher_source'=>$source]))->assertOk()
            ->assertViewHas('vouchers', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $id && (float) $rows->sum('total_amount') === (float) ($id * 100));
    }
    $this->get(route('monitoring',['tab'=>'supplier','voucher_source'=>'invalid']))->assertSessionHasErrors('voucher_source');
});


test('dashboard searches only affect their own sections and combine with invoice status', function () {
    DB::table('customer_supplier')->insert([['customer_id'=>1,'company_name'=>'Alpha'],['customer_id'=>2,'company_name'=>'Beta']]);
    foreach ([1,2] as $id) {
        DB::table('lo_inden_master')->insert(['lo_inden_id'=>$id,'lo_inden_no'=>'LO-'.$id,'customer_id'=>$id,'lo_inden_date'=>'2026-10-01','amount'=>$id*100]);
        DB::table('purchase_order_master')->insert(['purchase_order_id'=>$id,'po_no'=>'PO-'.$id,'customer_id'=>$id,'po_date'=>'2026-10-01','net_amount'=>$id*50]);
        dashboardInvoice($id,'2026-10-01',100);
        DB::table('invoice_master')->where('invoice_id',$id)->update(['customer_id'=>$id]);
    }
    dashboardReceipt(1,1,'2026-10-02',100);
    $query=['start'=>'2026-10-01','end'=>'2026-10-27','lo_q'=>'alpha','po_q'=>'PO-2','invoice_q'=>'Beta'];
    $response=$this->get(route('dashboard',$query))->assertOk();
    expect($response->viewData('loSummary')->amount)->toEqual(100)
        ->and($response->viewData('poSummary')->amount)->toEqual(100)
        ->and($response->viewData('balances')->total())->toBe(1)
        ->and($response->viewData('balances')->first()['number'])->toBe('INV-2')
        ->and($response->viewData('financial')[0][1])->toEqual(200)
        ->and($response->viewData('topCustomers'))->toHaveCount(2);
    expect($response->viewData('orderRecords')['lo']->pluck('number')->all())->toBe(['LO-1'])
        ->and($response->viewData('orderRecords')['po']->pluck('number')->all())->toBe(['PO-2']);
    $response->assertSee(route('lo.show', 1), false)->assertSee(route('purchase-order.show', 2), false);
    $empty=$this->get(route('dashboard', array_merge($query, ['lo_q'=>'not-found'])))->assertOk()
        ->assertSee('Tiada rekod sepadan dengan carian dan tempoh dipilih.');
    expect($empty->viewData('orderRecords')['lo'])->toHaveCount(0)
        ->and($empty->viewData('loSummary')->amount)->toEqual(0)
        ->and($empty->viewData('orderRecords')['po'])->toHaveCount(1);
    $query['invoice_q']='Alpha'; $query['status']='Selesai Dibayar';
    $this->get(route('dashboard',$query))->assertOk()->assertViewHas('balances',fn($rows)=>$rows->total()===1 && $rows->first()['number']==='INV-1');
    $query['invoice_q']='missing';
    $this->get(route('dashboard',$query))->assertOk()->assertViewHas('balances',fn($rows)=>$rows->total()===0);
    $this->get(route('dashboard',['lo_q'=>['invalid']]))->assertSessionHasErrors('lo_q');
});

test('dashboard ranking searches include matches outside the original top five', function () {
    foreach (range(1,6) as $id) {
        DB::table('customer_supplier')->insert(['customer_id'=>$id,'company_name'=>'Company '.$id]);
        DB::table('lo_inden_master')->insert(['lo_inden_id'=>$id,'lo_inden_no'=>'LO-'.$id,'customer_id'=>$id,'lo_inden_date'=>'2026-10-01','amount'=>$id*200]);
        dashboardInvoice($id,'2026-10-01',$id*100);
        DB::table('invoice_master')->where('invoice_id',$id)->update(['customer_id'=>$id]);
        DB::table('purchase_order_master')->insert(['purchase_order_id'=>$id,'po_no'=>'PO-'.$id,'customer_id'=>$id,'po_date'=>'2026-10-01','net_amount'=>$id*50]);
    }
    $response=$this->get(route('dashboard',['start'=>'2026-10-01','end'=>'2026-10-27','customer_q'=>'company 1','supplier_q'=>'company 2']))->assertOk();
    expect($response->viewData('topCustomers')->pluck('name')->all())->toBe(['Company 1'])
        ->and($response->viewData('topSuppliers')->pluck('name')->all())->toBe(['Company 2'])
        ->and($response->viewData('poSummary')->count)->toBe(6);
});


test('supervisor dashboard uses LO sales and PO purchases with descriptions and actual quotation decisions', function () {
    DB::table('customer_supplier')->insert(['customer_id'=>1,'company_name'=>'Alpha']);
    foreach ([1,2,3] as $id) {
        DB::table('sebutharga_master')->insert(['quotation_id'=>$id,'quotation_detail_id'=>$id,'quotation_no'=>'QT-'.$id]);
        DB::table('sebutharga_detail')->insert(['quotation_detail_id'=>$id,'quotation_id'=>$id,'quotation_date'=>'2026-10-01','quotation_title'=>'Projek eHilir','status_draft'=>'Final','sent_at'=>'2026-10-02','status_quotation'=>[1=>1,2=>0,3=>null][$id]]);
    }
    DB::table('lo_inden_master')->insert(['lo_inden_id'=>1,'lo_inden_no'=>'LO-1','customer_id'=>1,'quotation_detail_id'=>1,'lo_inden_date'=>'2026-10-01','amount'=>250000]);
    DB::table('purchase_order_master')->insert(['purchase_order_id'=>1,'po_no'=>'PO-1','customer_id'=>1,'po_date'=>'2026-10-01','net_amount'=>200000]);
    DB::table('purchase_order_item')->insert(['purchase_order_id'=>1,'item_description'=>'Server Dell R360']);
    dashboardInvoice(1,'2026-10-01',99);
    $response=$this->get(route('dashboard',['start'=>'2026-10-01','end'=>'2026-10-27']))->assertOk()
        ->assertSee('Projek eHilir')->assertSee('Server Dell R360')->assertSee('Trend Invois')->assertSee('Tempoh Baki Invois')->assertSee('Ringkasan Bayaran Invois');
    expect($response->viewData('loSummary')->amount)->toEqual(250000)
        ->and($response->viewData('poSummary')->amount)->toEqual(200000)
        ->and($response->viewData('topCustomers')->first()['amount'])->toEqual(250000)
        ->and($response->viewData('topSuppliers')->first()['amount'])->toEqual(200000)
        ->and($response->viewData('quotationStatus'))->toBe(['total'=>3,'waiting'=>1,'success'=>1,'failed'=>1]);
});

test('supplier financial summary excludes unpaid future and standalone vouchers', function () {
    DB::table('supplier_documents')->insert([
        ['id'=>1,'kind'=>'invoice','supplier_id'=>1,'supplier_name'=>'Alpha','document_no'=>'SI-1','document_date'=>'2026-09-01','total_amount'=>1000],
        ['id'=>2,'kind'=>'invoice','supplier_id'=>1,'supplier_name'=>'Alpha','document_no'=>'SI-2','document_date'=>'2026-11-01','total_amount'=>900],
    ]);
    foreach ([1=>['paid','2026-10-02',250],2=>['unpaid','2026-10-02',200],3=>['paid','2026-11-01',100],4=>['paid','2026-10-02',50]] as $id=>$values) {
        DB::table('payment_voucher_documents')->insert(['id'=>$id,'voucher_no'=>'PV-'.$id,'voucher_date'=>$values[1],'voucher_type'=>'supplier','supplier_payment_status'=>$values[0],'total_amount'=>$values[2]]);
        if ($id !== 4) DB::table('supplier_invoice_payments')->insert(['payment_voucher_id'=>$id,'supplier_document_id'=>1,'amount'=>$values[2]]);
    }
    $data=(new DashboardData())->build(CarbonImmutable::parse('2026-10-01'),CarbonImmutable::parse('2026-10-27'));
    expect($data['supplierTotals'])->toEqual(['total'=>1000,'paid'=>250,'balance'=>750]);
});
