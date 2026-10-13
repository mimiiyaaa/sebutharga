<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerDocumentLo
{
    public static function options()
    {
        return DB::table('lo_inden_master as lo')
            ->join('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->where('customer.jenis_customer', 1)
            ->orderByDesc('lo.lo_inden_date')->orderByDesc('lo.lo_inden_id')
            ->get(['lo.lo_inden_id', 'lo.lo_inden_no', 'lo.lo_inden_date', 'customer.company_name', 'customer.customer_name']);
    }

    public static function selected(Request $request): ?object
    {
        if (!$request->filled('lo_inden_id')) return null;
        $request->validate(['lo_inden_id' => 'required|integer']);
        $lo = DB::table('lo_inden_master as lo')
            ->join('customer_supplier as customer', 'customer.customer_id', '=', 'lo.customer_id')
            ->where('customer.jenis_customer', 1)->where('lo.lo_inden_id', $request->input('lo_inden_id'))
            ->select('lo.*', 'customer.company_name', 'customer.customer_name', 'customer.address')->first();
        if (!$lo) throw ValidationException::withMessages(['lo_inden_id' => __('Sila pilih Local Order yang sah.')]);
        return $lo;
    }

    public static function defaults(Request $request): array
    {
        $lo = self::selected($request);
        if (!$lo) return [];
        $values = ['lo_inden_id' => $lo->lo_inden_id, 'lo_reference' => $lo->lo_inden_no,
            'customer_id' => $lo->customer_id, 'customer_name' => $lo->company_name ?: $lo->customer_name,
            'customer_address' => $lo->address ?? ''];
        $items = collect();
        if ($lo->quotation_detail_id) {
            $draft = DB::table('sebutharga_detail')->where('quotation_detail_id', $lo->quotation_detail_id)->where('quotation_id', $lo->quotation_id)->first();
            if ($draft) {
                $snapshot = json_decode($draft->document_data ?? '{}', true) ?: [];
                foreach (['issuer_name', 'issuer_address', 'issuer_phone', 'issuer_email'] as $field) {
                    if (!empty($snapshot[$field])) $values[$field] = $snapshot[$field];
                }
                $values['customer_name'] = $snapshot['company_name'] ?? $values['customer_name'];
                $values['customer_address'] = $snapshot['address'] ?? $values['customer_address'];
                $items = DB::table('sebutharga_item')->where('quotation_detail_id', $lo->quotation_detail_id)
                    ->orderBy('quotation_item_id')->get()->map(fn ($item) => [
                        'description' => $item->item_description, 'quantity' => $item->quantity,
                        'unit' => $item->unit, 'price' => $item->unit_price, 'checked' => false,
                    ]);
            }
        }
        return ['values' => $values] + ($items->isNotEmpty() ? ['initialItems' => $items->all()] : []);
    }

    public static function bind(Request $request): void
    {
        $lo = self::selected($request);
        if (!$lo) return;
        if ((string) $request->input('customer_id') !== (string) $lo->customer_id) {
            throw ValidationException::withMessages(['customer_id' => __('Pelanggan mesti sama dengan Local Order yang dipilih.')]);
        }
        $request->merge(['lo_reference' => $lo->lo_inden_no]);
    }
}
