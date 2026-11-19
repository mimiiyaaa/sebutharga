<?php

namespace App\Services;

use Carbon\Carbon;

class SupplierDocumentParser
{
    public function parse(string $text, string $kind): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), fn ($line) => $line !== ''));
        $joined = implode("\n", $lines);
        preg_match('/(?:No\.?\s*Dokumen|Invoice\s*(?:No\.?|#)|DO\s*(?:No\.?|#)|No\.?\s*(?:Invois|Invoice|DO)|No\.)\s*:?\s*([A-Z0-9][A-Z0-9._\/-]*)/i', $joined, $number);
        preg_match('/(?:Tarikh|Date)\s*:\s*(\d{1,4}[\/.-]\d{1,2}[\/.-]\d{1,4}|\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $joined, $date);
        $first = $lines[0] ?? '';
        $supplier = trim(preg_split('/\s+(?:NOTA SERAHAN|DELIVERY ORDER|INVOIS|INVOICE)\b/i', $first)[0]);
        if (preg_match('/^(?:NOTA SERAHAN|DELIVERY ORDER|INVOIS|INVOICE)\b/i', $supplier)) $supplier = '';
        preg_match('/(?:JUMLAH BESAR|GRAND TOTAL|TOTAL AMOUNT|AMOUNT DUE)\s*(?:\(RM\)|RM)?\s*:?\s*([\d,]+\.\d{2})/i', $joined, $total);
        $descriptions = []; $metrics = []; $pending = []; $inTable = false; $endMarkers = null;
        $numeric = '[\d,]+(?:\.\d+)?';
        $tail = $kind === 'invoice'
            ? '/\s+('.$numeric.')\s+([\pL][\pL.\/-]*)\s+(?:RM\s*)?('.$numeric.')\s+(?:RM\s*)?('.$numeric.')\s*$/u'
            : '/\s+('.$numeric.')\s+([\pL][\pL.\/-]*)(?:\s+[\/✓])?\s*$/u';
        foreach ($lines as $line) {
            if (preg_match('/\b(?:BIL|NO\.?)\b.*(?:KETERANGAN|DESCRIPTION)|(?:KETERANGAN|DESCRIPTION).*KUANTITI/i', $line)) { $inTable = true; continue; }
            if (!$inTable) continue;
            if (preg_match('/^(?:JUMLAH BESAR|GRAND TOTAL|SUBTOTAL|TOTAL|NOTA:|NOTE:|ARAHAN|PAYMENT|DIHANTAR|DISEDIAKAN|DITERIMA)/i', $line)) break;
            if (preg_match('/^(?:HARGA|SEUNIT|JUMLAH|KUANTITI|QUANTITY|UNIT|SEMAKAN|\(RM\))\b|^\(RM\)$/i', $line)) continue;
            if (preg_match($tail, ' '.$line, $match)) {
                $metrics[] = ['quantity' => (float) str_replace(',', '', $match[1]), 'unit' => $match[2], 'price' => $kind === 'invoice' ? (float) str_replace(',', '', $match[3]) : 0];
                $line = trim(substr(' '.$line, 0, -strlen($match[0])));
            }
            if ($line === '') continue;
            if (preg_match('/^\d+\s+(.+)$/u', $line, $row)) {
                $endMarkers ??= count($pending) > 0;
                if ($endMarkers) {
                    $pending[] = $row[1]; $descriptions[] = implode("\n", $pending); $pending = [];
                } else {
                    if ($pending) $descriptions[] = implode("\n", $pending);
                    $pending = [$row[1]];
                }
            } else { $pending[] = $line; }
        }
        if ($pending) $descriptions[] = implode("\n", $pending);
        $items = [];
        if (count($descriptions) === count($metrics)) {
            foreach ($descriptions as $i => $description) $items[] = ['description' => $description] + $metrics[$i];
        }
        return ['supplier_name' => $supplier, 'document_no' => $number[1] ?? '', 'document_date' => $this->date($date[1] ?? ''),
            'total_amount' => isset($total[1]) ? (float) str_replace(',', '', $total[1]) : null, 'items' => $items];
    }

    private function date(string $date): string
    {
        $date = str_ireplace(['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'], ['January','February','March','April','May','June','July','August','September','October','November','December'], $date);
        foreach (['!Y-m-d','!d/m/Y','!d-m-Y','!d.m.Y','!j F Y','!j M Y'] as $format) {
            try { $parsed = Carbon::createFromFormat($format, $date); if ($parsed && $parsed->format(ltrim($format, '!')) === $date) return $parsed->format('Y-m-d'); } catch (\Throwable) {}
        }
        return '';
    }
}
