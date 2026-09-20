<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class QuotationDocument
{
    public const FIELDS = ['issuer_name', 'issuer_phone', 'issuer_email', 'issuer_person_in_charge', 'issuer_address', 'customer_name', 'company_name', 'phone_no', 'email', 'address', 'additional_terms', 'maklumat_tambahan', 'catatan', 'disediakan_role', 'disediakan_company_name', 'diterima_role'];

    public static function capture(Request $request): string
    {
        $rules = [];
        foreach (self::FIELDS as $field) {
            $rules[$field] = ['nullable', 'string', in_array($field, ['address', 'issuer_address', 'additional_terms', 'maklumat_tambahan', 'catatan']) ? 'max:10000' : 'max:255'];
        }
        $data = $request->validate($rules);
        return json_encode(array_replace(array_fill_keys(self::FIELDS, null), $data), JSON_THROW_ON_ERROR);
    }

    public static function data(?object $draft): array
    {
        return json_decode($draft->document_data ?? '{}', true) ?: [];
    }
}
