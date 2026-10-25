<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
    DB::purge('sqlite');
    Schema::create('customer_supplier', function (Blueprint $table) {
        $table->increments('customer_id');
        $table->integer('jenis_customer');
        foreach (['company_name', 'customer_name', 'address', 'phone_no', 'email', 'reference_no'] as $field) {
            $table->string($field)->nullable();
        }
    });
    Schema::create('companies', function (Blueprint $table) {
        $table->increments('company_id'); $table->string('nama_syarikat');
    });
    Schema::create('sebutharga_master', function (Blueprint $table) {
        $table->increments('quotation_id'); $table->string('quotation_no');
    });
    Schema::create('sebutharga_detail', function (Blueprint $table) {
        $table->increments('quotation_detail_id'); $table->integer('quotation_id');
    });
    foreach ([
        '2026_09_10_081900_create_purchase_order_master_table.php',
        '2026_09_10_083619_create_purchase_order_item_table.php',
        '2026_09_20_130000_create_pembekal_quotation_tables.php',
        '2026_09_22_100000_add_sst_to_purchase_order_master.php',
        '2026_10_06_120000_add_document_data_to_purchase_order_master.php',
        '2026_10_11_120000_link_purchase_order_to_supplier_quotation.php',
        '2026_10_13_130000_add_quotation_date_to_pembekal_quotation_master.php',
    ] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }
    DB::table('customer_supplier')->insert(['customer_id' => 1, 'jenis_customer' => 2, 'company_name' => 'Pembekal Ujian']);
    DB::table('pembekal_quotation_master')->insert(['supplier_quotation_id' => 1, 'supplier_id' => 1, 'quotation_no_supplier' => 'SQ-001', 'company_name' => 'Pembekal Ujian', 'created_at' => now()]);
    DB::table('pembekal_quotation_item')->insert(['supplier_quotation_id' => 1, 'item_description' => 'Komputer', 'quantity' => 2, 'unit' => 'Unit', 'unit_price' => 100, 'subtotal' => 200]);
    $user = new User(); $user->id = 42; $user->name = 'Tester';
    $this->actingAs($user);
    $this->poData = ['supplier_quotation_id' => 1, 'customer_id' => 1, 'po_date' => '2026-10-12', 'delivery_days' => 7, 'payment_days' => 30,
        'items_json' => json_encode([['description' => 'Komputer', 'quantity' => 2, 'unit' => 'Unit', 'price' => 100]])];
});

test('new purchase orders validate missing supplier quotation without a server error', function () {
    $data = $this->poData;
    unset($data['supplier_quotation_id']);
    $this->post(route('purchase-order.store'), $data)->assertSessionHasErrors('supplier_quotation_id');
    $data['supplier_quotation_id'] = null;
    $this->post(route('purchase-order.store'), $data)->assertSessionHasErrors('supplier_quotation_id');
    $this->assertDatabaseCount('purchase_order_master', 0);
});

test('supplier quotation purchase orders can be selected created and updated', function () {
    $this->get(route('purchase-order.create'))->assertOk()->assertSee('SQ-001');
    $this->get(route('purchase-order.form', ['supplier_quotation_id' => 1]))->assertOk()->assertSee('Komputer');
    $this->post(route('purchase-order.store'), $this->poData)->assertSessionHasNoErrors()->assertRedirect(route('purchase-order.index'));
    $this->assertDatabaseHas('purchase_order_master', ['purchase_order_id' => 1, 'supplier_quotation_id' => 1, 'quotation_id' => null, 'net_amount' => 200]);
    $this->get(route('purchase-order.index'))->assertOk()->assertSee('SQ-001');
    $this->get(route('purchase-order.edit', 1))->assertOk()->assertSee('Komputer');
    $data = $this->poData; unset($data['supplier_quotation_id']);
    $data['items_json'] = json_encode([['description' => 'Komputer baharu', 'quantity' => 3, 'unit' => 'Unit', 'price' => 150]]);
    $this->put(route('purchase-order.update', 1), $data)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('purchase_order_master', ['purchase_order_id' => 1, 'supplier_quotation_id' => 1, 'net_amount' => 450]);
    $this->assertDatabaseCount('purchase_order_item', 1);
});

test('legacy purchase orders remain editable without a supplier quotation', function () {
    $this->post(route('purchase-order.store'), $this->poData)->assertSessionHasNoErrors();
    DB::table('sebutharga_master')->insert(['quotation_id' => 1, 'quotation_no' => 'LEGACY-001']);
    DB::table('sebutharga_detail')->insert(['quotation_detail_id' => 1, 'quotation_id' => 1]);
    DB::table('purchase_order_master')->where('purchase_order_id', 1)->update(['supplier_quotation_id' => null, 'quotation_id' => 1, 'quotation_detail_id' => 1]);
    $data = $this->poData; unset($data['supplier_quotation_id']);
    $this->put(route('purchase-order.update', 1), $data)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('purchase_order_master', ['purchase_order_id' => 1, 'supplier_quotation_id' => null, 'quotation_id' => 1, 'quotation_detail_id' => 1]);
});
