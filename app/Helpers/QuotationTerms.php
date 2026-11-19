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
        $total = collect($request->input('items', []))->sum(fn ($item) => max(0, (float) ($item['quantity'] ?? 0)) * max(0, (float) ($item['price'] ?? 0)));
        $priceLine = 'Jumlah sebut harga: RM' . number_format($total, 2) . '.';
        $text = '';
        if ($containsCompleteTerms) {
            $cleanLines = collect($lines)
                ->map(fn ($line) => preg_replace('/^\s*\d+\.\s*/', '', trim($line)))
                ->filter()
                ->reject(fn ($line) => str_starts_with(strtolower($line), 'harga dalam ringgit malaysia') || str_starts_with(strtolower($line), 'jumlah sebut harga:'))
                ->values();
            $cleanLines->prepend($priceLine);
            $text = $cleanLines->map(fn ($line, $index) => ($index + 1) . '. ' . $line)->implode("\n");
        } else {
            $text = "1. {$priceLine}\n2. Tempoh sah sebutharga adalah selama {$days['validity_days']} hari dari tarikh sebutharga dikeluarkan.\n3. Tempoh penghantaran: {$days['delivery_min_days']} - {$days['delivery_max_days']} hari selepas penerimaan Pesanan Belian (PO) rasmi.\n4. Sila tandatangan di bawah untuk pengesahan persetujuan sebutharga ini.";
            foreach ($lines as $line) {
                $line = preg_replace('/^\s*\d+\.\s*/', '', trim($line));
                if ($line !== '') $text .= "\n" . (substr_count($text, "\n") + 2) . '. ' . $line;
            }
        }
        return $text;
    }
}
