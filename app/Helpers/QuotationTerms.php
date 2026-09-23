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
        $additionalTerms = trim($days['additional_terms'] ?? '');
        $lines = preg_split('/\R/u', $additionalTerms) ?: [];
        $containsCompleteTerms = isset($lines[0]) && preg_match('/^\s*1\.\s*/', $lines[0]);
        $text = '';
        $number = $containsCompleteTerms ? 1 : 4;
        if (!$containsCompleteTerms) {
            $text = "1. Tempoh sah sebutharga adalah selama {$days['validity_days']} hari dari tarikh sebutharga dikeluarkan.\n2. Tempoh penghantaran: {$days['delivery_min_days']} - {$days['delivery_max_days']} hari selepas penerimaan Pesanan Belian (PO) rasmi.\n3. Sila tandatangan di bawah untuk pengesahan persetujuan sebutharga ini.";
        }
        foreach ($lines as $line) {
            $line = preg_replace('/^\s*\d+\.\s*/', '', trim($line));
            if ($line !== '') $text .= "\n" . $number++ . '. ' . $line;
        }
        return $text;
    }
}
