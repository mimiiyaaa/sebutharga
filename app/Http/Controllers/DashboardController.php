<?php

namespace App\Http\Controllers;

use App\Services\DashboardData;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardData $dashboard)
    {
        $today = CarbonImmutable::now('Asia/Kuala_Lumpur')->startOfDay();
        $data = $request->validate([
            'start' => ['nullable', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.$today->toDateString()],
            'panel' => ['nullable', Rule::in(['drafts', 'finals', 'lo', 'po', 'missing-do', 'missing-invoice', 'review'])],
            'status' => ['nullable', Rule::in(['Selesai Dibayar', 'Bayaran Sebahagian', 'Belum Dibayar', 'Perlu Semakan'])],
            'age' => ['nullable', 'integer', 'between:0,3'],
        ]);
        $end = CarbonImmutable::parse($data['end'] ?? $today->toDateString());
        $start = CarbonImmutable::parse($data['start'] ?? $end->subMonthsNoOverflow(5)->startOfMonth()->toDateString());
        if ($start > $end || $start->diffInDays($end) > 366) {
            throw ValidationException::withMessages(['start' => __('Pilih julat tarikh yang sah, maksimum 366 hari.')]);
        }
        $result = $dashboard->build($start, $end);
        $rows = $result['balances']->filter(fn ($row) =>
            ($request->filled('status') ? $row['status'] === $data['status'] : ($row['balance'] > 0 || $row['status'] === 'Perlu Semakan'))
            && (!$request->filled('age') || $row['bucket'] === (int) $data['age']))->values();
        $paginate = fn ($records, $name) => new LengthAwarePaginator(
            $records->forPage(max(1, $request->integer($name, 1)), 10)->values(), $records->count(), 10,
            max(1, $request->integer($name, 1)), ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $name]);
        return view('pages.dashboard.ecommerce', array_merge($result, [
            'title' => __('Dashboard'), 'start' => $start, 'end' => $end,
            'balances' => $paginate($rows, 'page'), 'selectedStatus' => $data['status'] ?? '', 'selectedAge' => $data['age'] ?? '',
            'panel' => $data['panel'] ?? null,
            'panelRows' => isset($data['panel']) ? $paginate($result['groups'][$data['panel']], 'records_page') : null,
        ]));
    }
}
