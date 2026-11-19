<?php

use App\Http\Controllers\GlobalSearchController;

test('search results accept documents without a status field', function () {
    $row = (object) [
        'reference' => 'SQ-001', 'subject' => 'Equipment',
        'party' => 'Supplier', 'date' => '2026-10-27',
    ];
    $method = new ReflectionMethod(GlobalSearchController::class, 'result');
    $result = $method->invoke(new GlobalSearchController(), 'Sebut Harga Pembekal', $row, '/quotation/1');

    expect($result['status'])->toBeNull()
        ->and($result['reference'])->toBe('SQ-001');

    $row->status = 0;
    $result = $method->invoke(new GlobalSearchController(), 'Sebut Harga', $row, '/quotation/1');
    expect($result['status'])->toBe(0);
});
