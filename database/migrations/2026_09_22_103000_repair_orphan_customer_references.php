<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sebutharga_master')->orderBy('quotation_id')->each(function ($master) {
            $masterCustomerExists = DB::table('customer_supplier')
                ->where('customer_id', $master->customer_id)
                ->exists();

            if ($masterCustomerExists) {
                return;
            }

            $snapshot = DB::table('sebutharga_detail')
                ->where('quotation_id', $master->quotation_id)
                ->whereNotNull('document_data')
                ->orderByRaw('quotation_detail_id = ? DESC', [$master->quotation_detail_id])
                ->orderByDesc('quotation_detail_id')
                ->get(['document_data'])
                ->map(fn ($draft) => json_decode((string) $draft->document_data, true))
                ->first(fn ($data) => is_array($data) && ! empty($data['company_name']));

            if (! $snapshot) {
                return;
            }

            $customerQuery = DB::table('customer_supplier')
                ->where('jenis_customer', 1)
                ->where('company_name', $snapshot['company_name']);

            if (! empty($snapshot['email'])) {
                $customerQuery->where('email', $snapshot['email']);
            }

            $customer = $customerQuery->first();

            if (! $customer) {
                $matches = DB::table('customer_supplier')
                    ->where('jenis_customer', 1)
                    ->where('company_name', $snapshot['company_name'])
                    ->get();
                $customer = $matches->count() === 1 ? $matches->first() : null;
            }

            if (! $customer) {
                return;
            }

            DB::table('sebutharga_master')
                ->where('quotation_id', $master->quotation_id)
                ->update([
                    'customer_id' => $customer->customer_id,
                    'updated_at' => now(),
                ]);

            DB::table('sebutharga_detail')
                ->where('quotation_id', $master->quotation_id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('customer_supplier')
                        ->whereColumn('customer_supplier.customer_id', 'sebutharga_detail.customer_id');
                })
                ->update([
                    'customer_id' => $customer->customer_id,
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        // The original orphaned customer IDs cannot be restored safely.
    }
};
