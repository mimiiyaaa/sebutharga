<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class QuotationNumber
{
    public static function sequences(): array
    {
        $sequences = [];
        foreach (DB::table('sebutharga_master')->pluck('quotation_no') as $number) {
            if (preg_match('/^K1R-QT-(\d{4})-(\d+)$/', $number, $matches)) {
                $year = $matches[1];
                $sequences[$year] = max($sequences[$year] ?? 0, (int) $matches[2]);
            }
        }
        return $sequences;
    }

    public static function next(string $date): string
    {
        $year = date('Y', strtotime($date));
        $next = (self::sequences()[$year] ?? 0) + 1;
        return 'K1R-QT-'.$year.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
