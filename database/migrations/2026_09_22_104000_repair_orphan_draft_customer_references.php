<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sebutharga_detail as detail')
            ->leftJoin('customer_supplier as detail_customer', 'detail_customer.customer_id', '=', 'detail.customer_id')
            ->join('sebutharga_master as master', 'master.quotation_id', '=', 'detail.quotation_id')
            ->join('customer_supplier as master_customer', 'master_customer.customer_id', '=', 'master.customer_id')
            ->whereNull('detail_customer.customer_id')
            ->where('master_customer.jenis_customer', 1)
            ->update([
                'detail.customer_id' => DB::raw('master.customer_id'),
                'detail.updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // The original orphaned customer IDs cannot be restored safely.
    }
};
