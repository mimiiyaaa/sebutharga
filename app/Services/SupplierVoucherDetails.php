<?php
namespace App\Services;

class SupplierVoucherDetails
{
    public static function values(?object $document = null, ?object $supplier = null): array
    {
        $saved = json_decode($document->supplier_details ?? '{}', true) ?: [];
        return $saved + [
            'issuer_name' => config('purchase_order.issuer_name'),
            'issuer_address' => config('purchase_order.issuer_address'),
            'issuer_phone' => config('purchase_order.issuer_phone'),
            'issuer_email' => config('purchase_order.issuer_email'),
            'supplier_name' => $supplier->company_name ?? $document->payee_name ?? '',
            'supplier_person' => $supplier->customer_name ?? '',
            'supplier_address' => $supplier->address ?? '',
            'supplier_phone' => $supplier->phone_no ?? '',
            'supplier_email' => $supplier->email ?? '',
        ];
    }
}
