<?php

namespace App\Helpers;

class QuotationTerms
{
    public static function periods(?string $text): array
    {
        preg_match('/selama (\d+) hari/', $text ?? '', $validity);
        preg_match('/penghantaran:\s*(\d+)\s*-\s*(\d+) hari/i', $text ?? '', $delivery);
        return ['validity_days' => (int) ($validity[1] ?? 30), 'delivery_min_days' => (int) ($delivery[1] ?? 14), 'delivery_max_days' => (int) ($delivery[2] ?? 30)];
    }

    public static function fromRequest(\Illuminate\Http\Request $request): string
    {
        $days = $request->validate([
            'additional_terms' => ['nullable', 'string', 'max:10000'],
            'validity_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'delivery_min_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'delivery_max_days' => ['required', 'integer', 'gte:delivery_min_days', 'max:3650'],
        ]);
        $text = "1. Tempoh sah sebutharga adalah selama {$days['validity_days']} hari dari tarikh sebutharga dikeluarkan.\n2. Tempoh penghantaran: {$days['delivery_min_days']} - {$days['delivery_max_days']} hari selepas penerimaan Pesanan Belian (PO) rasmi.\n3. Sila tandatangan di bawah untuk pengesahan persetujuan sebutharga ini.";
        $number = 4;
        foreach (preg_split('/\R/u', $days['additional_terms'] ?? '') as $line) {
            if (trim($line) !== '') $text .= "\n" . $number++ . '. ' . trim($line);
        }
        return $text;
    }
}
