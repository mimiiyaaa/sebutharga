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
    Schema::create('payment_voucher_documents', function (Blueprint $t) { $t->increments('id'); $t->string('voucher_no'); $t->date('voucher_date'); });
    Schema::create('payment_voucher_invoice', function (Blueprint $t) { $t->increments('id'); $t->integer('invoice_id'); $t->integer('payment_voucher_id'); $t->decimal('amount',12,2)->nullable(); });
    Schema::create('lo_inden_master', function (Blueprint $t) { $t->increments('lo_inden_id'); $t->string('lo_inden_no'); $t->integer('customer_id'); $t->integer('quotation_id')->nullable(); $t->integer('quotation_detail_id')->nullable(); $t->date('lo_inden_date'); });
    Schema::create('purchase_order_master', function (Blueprint $t) { $t->increments('purchase_order_id'); $t->string('po_no'); $t->date('po_date'); });
    Schema::create('sebutharga_master', function (Blueprint $t) { $t->increments('quotation_id'); $t->integer('quotation_detail_id')->nullable(); $t->string('quotation_no'); });
    Schema::create('sebutharga_detail', function (Blueprint $t) { $t->increments('quotation_detail_id'); $t->integer('quotation_id'); $t->date('quotation_date'); $t->string('quotation_title')->nullable(); $t->string('status_draft'); $t->timestamp('sent_at')->nullable(); $t->integer('status_quotation')->nullable(); });
    Schema::create('delivery_order_master', function (Blueprint $t) { $t->increments('delivery_order_id'); $t->integer('customer_id'); $t->string('document_kind'); $t->date('do_date'); $t->text('document_data')->nullable(); });
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
    $this->get(route('monitoring'))->assertOk()->assertSee('Pemantauan Bayaran dan Reminder')->assertDontSee('Data contoh')->assertSee('Tiada peringatan bayaran buat masa ini.');
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
    expect($data['summary'])->toBe(['unpaid' => 2, 'unpaid_total' => 200, 'overdue' => 1, 'overdue_total' => 100, 'upcoming' => 1, 'upcoming_total' => 100, 'review' => 1, 'review_total' => 100, 'paid' => 1, 'paid_total' => 100])
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
        $response->assertSee('href="'.route($destination).'"', false);
        $this->get(route('dashboard', ['panel' => $panel]))->assertRedirect(route($destination));
    }
});
