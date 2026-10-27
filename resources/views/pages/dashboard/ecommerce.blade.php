@extends('layouts.app')

@section('content')
@php
    $dashboardLink = fn ($extra = []) => route('dashboard', array_merge(['start' => $start->toDateString(), 'end' => $end->toDateString()], $extra));
    $statusColors = ['Selesai Dibayar' => 'stroke-success-500', 'Bayaran Sebahagian' => 'stroke-brand-500', 'Belum Dibayar' => 'stroke-warning-400', 'Perlu Semakan' => 'stroke-error-500'];
@endphp
<div class="space-y-6 font-outfit">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white/90">{{ __('Dashboard') }}</h1>
        <span class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-theme-xs font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Dikemas kini') }}: {{ now('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</span>
    </div>
    <section class="relative isolate overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="pointer-events-none absolute inset-y-0 end-0 w-1/2 bg-gradient-to-t from-brand-50 to-white dark:from-brand-500/10 dark:to-gray-900" aria-hidden="true"></div>
        <div class="relative grid items-center gap-4 px-6 py-7 sm:grid-cols-[minmax(0,1fr)_240px] sm:px-8 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-widest text-brand-600 dark:text-brand-400">K1 Resources</p>
                <h2 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-gray-900 dark:text-white/90 lg:text-4xl">{{ __('Selamat datang kembali') }}!</h2>
                <p class="mt-4 max-w-lg text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Pantau urusan dokumen, penerimaan pelanggan dan baki invois dalam satu paparan.') }}</p>
                <div class="mt-5 flex flex-wrap gap-2 text-theme-xs text-gray-600 dark:text-gray-400">
                    <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">{{ __('Tempoh carta') }}: {{ $start->format('d/m/Y') }} – {{ $end->format('d/m/Y') }}</span>
                    <span class="rounded-lg bg-brand-50 px-3 py-2 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Baki setakat') }} {{ $end->format('d/m/Y') }}</span>
                </div>
            </div>
            <img src="{{ asset('images/dashboard-mascot.png') }}" alt="{{ __('Maskot dashboard') }}" class="hidden h-36 w-full max-w-[160px] justify-self-end object-contain drop-shadow-sm sm:block lg:h-44">
        </div>
    </section>

    <section class="grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 lg:grid-cols-2 dark:border-gray-800 dark:bg-gray-900">
        <form method="GET" action="{{ route('carian') }}" class="flex items-end gap-2">
            <div class="min-w-0 flex-1"><label for="dashboard-search" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Carian dokumen') }}</label><input id="dashboard-search" name="q" required maxlength="200" placeholder="{{ __('No. dokumen, pelanggan atau keterangan item') }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
            <button class="document-action document-action-primary">{{ __('Cari') }}</button>
        </form>
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-2">
            @foreach (['start' => 'Tarikh Mula', 'end' => 'Tarikh Akhir'] as $field => $label)
                <div class="min-w-32 flex-1"><label for="dashboard-{{ $field }}" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __($label) }}</label><input id="dashboard-{{ $field }}" type="date" name="{{ $field }}" required value="{{ ${$field}->toDateString() }}" max="{{ now('Asia/Kuala_Lumpur')->toDateString() }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></div>
            @endforeach
            <button class="document-action document-action-primary">{{ __('Terapkan') }}</button><a class="document-action" href="{{ route('dashboard') }}">{{ __('Reset') }}</a>
        </form>
        <p class="text-theme-xs text-gray-500 lg:col-span-2 dark:text-gray-400">{{ __('Carta mengikut julat tarikh. Ringkasan dan baki merangkumi rekod hingga tarikh akhir; status dokumen mengikut keadaan semasa.') }}</p>
    </section>
    @if($pendingCount)
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-800 dark:bg-warning-500/10 dark:text-warning-400">{{ $pendingCount }} {{ __('baucar perlu semakan. Amaun yang belum disahkan tidak ditolak daripada baki invois.') }} <a class="underline" href="{{ $dashboardLink(['panel' => 'review']) }}#dashboard-records">{{ __('Semak') }}</a></div>
    @endif
    @php
        $metricThemes = [
            ['border-warning-200 bg-warning-50 dark:border-warning-900 dark:bg-warning-500/10', 'bg-warning-500 text-white', 'text-warning-700 dark:text-warning-400', 'M8 5h10a2 2 0 0 1 2 2v13H6V7a2 2 0 0 1 2-2Zm2-2v4m6-4v4M9 11h8m-8 4h5'],
            ['border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-500/10', 'bg-brand-500 text-white', 'text-brand-700 dark:text-brand-300', 'M14 3H6v18h12V7l-4-4Zm0 0v5h5M8 14l3 3 5-6'],
            ['border-success-200 bg-success-50 dark:border-success-900 dark:bg-success-500/10', 'bg-success-600 text-white', 'text-success-700 dark:text-success-400', 'M3 7h18v14H3V7Zm2-4h14v4H5V3Zm4 9h6m-6 4h6'],
            ['border-blue-light-200 bg-blue-light-50 dark:border-blue-light-900 dark:bg-blue-light-500/10', 'bg-blue-light-600 text-white', 'text-blue-light-700 dark:text-blue-light-400', 'M3 3h2l3 12h10l3-8H6m3 13h1m6 0h1'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as [$label, $value, $hint, $tag, $group])
            @php([$surface, $icon, $ink, $path] = $metricThemes[$loop->index])
            <a href="{{ $dashboardLink(['panel' => $group]) }}#dashboard-records" class="block rounded-2xl border p-5 shadow-theme-xs transition hover:shadow-theme-md focus-visible:outline-2 focus-visible:outline-brand-500 {{ $surface }}">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl shadow-theme-xs {{ $icon }}"><svg class="h-6 w-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="{{ $path }}" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                    <span class="rounded-full bg-white/70 px-3 py-1 text-theme-xs font-semibold dark:bg-gray-900/40 {{ $ink }}">{{ __($tag) }}</span>
                </div>
                <div class="mt-5 flex items-baseline justify-between gap-2"><h2 class="text-sm font-semibold {{ $ink }}">{{ __($label) }}</h2><p class="text-3xl font-semibold tabular-nums text-gray-900 dark:text-white/90">{{ $value }}</p></div>
                <p class="mt-3 border-t border-current/10 pt-3 text-theme-xs {{ $ink }}">{{ __($hint) }}</p>
            </a>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="border-s-4 border-brand-500 ps-3 text-base font-semibold text-gray-900 dark:border-brand-400 dark:text-white/90">{{ __('Kedudukan Bayaran Pelanggan') }}</h2><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Berdasarkan invois kepada pelanggan') }}</span></div>
        <div class="grid gap-5 p-6 md:grid-cols-3">
            @foreach ($financial as [$label, $amount, $hint])
                <div class="rounded-xl bg-gray-50 p-5 dark:bg-gray-800/50"><p class="text-sm text-gray-600 dark:text-gray-400">{{ __($label) }}</p><p @class(['mt-2 text-2xl font-semibold tabular-nums', 'text-gray-900 dark:text-white/90' => $loop->first, 'text-success-600 dark:text-success-400' => $loop->iteration === 2, 'text-warning-600 dark:text-warning-400' => $loop->last])><span class="text-sm font-medium">RM</span> {{ number_format($amount, 2) }}</p><p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($hint) }}</p></div>
            @endforeach
        </div>
    </section>

    <div class="grid items-stretch gap-6 xl:grid-cols-3">
        <section class="min-w-0 rounded-2xl border border-gray-200 bg-white p-6 xl:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Trend Invois & Penerimaan') }}</h2>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Perbandingan bulanan · RM ribu') }}</p>
            <div class="mt-4 flex flex-wrap gap-4 text-theme-xs text-gray-600 dark:text-gray-400"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-brand-500"></span>{{ __('Invois Dikeluarkan') }}</span><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-success-500"></span>{{ __('Bayaran Diterima') }}</span></div>
            <svg viewBox="0 0 620 260" class="mt-4 w-full" role="img" aria-labelledby="trend-chart-title">
                <title id="trend-chart-title">{{ __('Trend Invois & Penerimaan') }}</title>
                @foreach (range(0, 5) as $step)
                    @php($tick = $chartMax * $step / 5)
                    <line x1="40" x2="605" y1="{{ 220 - $tick / $chartMax * 200 }}" y2="{{ 220 - $tick / $chartMax * 200 }}" class="stroke-gray-100 dark:stroke-gray-800" />
                    <text x="30" y="{{ 224 - $tick / $chartMax * 200 }}" text-anchor="end" class="fill-gray-400 text-theme-xs dark:fill-gray-500">{{ number_format($tick, 1) }}</text>
                @endforeach
                @foreach ($months as $i => $month)
                    <rect x="{{ 50 + $i * (550 / count($months)) }}" y="{{ 220 - $invoiced[$i] / $chartMax * 200 }}" width="{{ 180 / count($months) }}" height="{{ $invoiced[$i] / $chartMax * 200 }}" rx="4" class="fill-brand-500"><title>{{ __($month) }}: {{ __('Invois') }} RM {{ number_format($invoiced[$i] * 1000) }}</title></rect>
                    <rect x="{{ 50 + $i * (550 / count($months)) + (220 / count($months)) }}" y="{{ 220 - $received[$i] / $chartMax * 200 }}" width="{{ 180 / count($months) }}" height="{{ $received[$i] / $chartMax * 200 }}" rx="4" class="fill-success-500"><title>{{ __($month) }}: {{ __('Bayaran Diterima') }} RM {{ number_format($received[$i] * 1000) }}</title></rect>
                    <text x="{{ 50 + $i * (550 / count($months)) + (220 / count($months)) }}" y="247" text-anchor="middle" class="fill-gray-500 text-theme-xs dark:fill-gray-400">{{ __($month) }}</text>
                @endforeach
            </svg>
            <p class="mt-2 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Bayaran diterima pada sesuatu bulan boleh merujuk kepada invois bulan terdahulu.') }}</p>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Status Invois Pelanggan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Setakat') }} {{ $end->format('d/m/Y') }}</p>
            <div class="relative mx-auto my-5 h-40 w-40">
                <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90" role="img" aria-label="{{ __('Status Invois Pelanggan') }}">
                    <circle cx="60" cy="60" r="48" fill="none" stroke-width="13" class="stroke-gray-100 dark:stroke-gray-800" />
                    @php($offset = 0)
                    @foreach($statuses as $label => $count)
                        @if($count > 0)<circle cx="60" cy="60" r="48" fill="none" stroke-width="13" pathLength="{{ max(1, $invoiceCount) }}" stroke-dasharray="{{ $count }} {{ $invoiceCount - $count }}" stroke-dashoffset="{{ -$offset }}" class="{{ $statusColors[$label] }}"><title>{{ __($label) }}: {{ $count }}</title></circle>@endif
                        @php($offset += $count)
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center"><span class="text-3xl font-semibold text-gray-900 dark:text-white/90">{{ $invoiceCount }}</span><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Invois') }}</span></div>
            </div>
            <div class="space-y-3 text-sm">
                @foreach($statuses as $label => $count)
                    <a href="{{ $dashboardLink(['status' => $label]) }}#invoice-balances" class="flex items-center gap-2 text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-300"><svg class="h-3 w-3 {{ $statusColors[$label] }}" viewBox="0 0 12 12" aria-hidden="true"><circle cx="6" cy="6" r="4" fill="none" stroke-width="3" /></svg><span class="flex-1">{{ __($label) }}</span><span class="font-semibold">{{ $count }}</span></a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Tempoh Baki Invois') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Umur dikira daripada tarikh invois, bukan tarikh lewat bayar.') }}</p>
            <div class="mt-6 space-y-5">
                @foreach ($aging as [$label, $amount, $width])
                    <a href="{{ $dashboardLink(['age' => $loop->index]) }}#invoice-balances" class="block"><div class="mb-2 flex justify-between gap-3 text-sm"><span class="text-gray-600 dark:text-gray-400">{{ __($label) }}</span><span class="font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format($amount, 2) }}</span></div><svg viewBox="0 0 280 8" class="h-2 w-full" preserveAspectRatio="none" role="img" aria-label="{{ __($label) }}: RM {{ number_format($amount, 2) }}"><rect width="280" height="8" rx="4" class="fill-gray-100 dark:fill-gray-800"/><rect width="{{ $width }}" height="8" rx="4" @class(['fill-brand-500' => !$loop->last, 'fill-warning-500' => $loop->last]) /></svg></a>
                @endforeach
            </div>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Perlu Tindakan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Senarai semakan urusan yang belum lengkap') }}</p>
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($actions as [$label, $count, $hint, $group])
                    <div class="flex items-center gap-4 py-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $count }}</span><div class="min-w-0 flex-1"><h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __($label) }}</h3><p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($hint) }}</p></div><a href="{{ $dashboardLink(['panel' => $group]) }}#dashboard-records" class="document-action">{{ __('Lihat') }}</a></div>
                @endforeach
            </div>
        </section>
    </div>

    <section id="invoice-balances" class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5"><div><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $selectedStatus ? __($selectedStatus) : __('Invois Berbaki — Keutamaan Susulan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Disusun mengikut umur tertinggi') }} · {{ $balances->total() }} {{ __('rekod') }}</p></div><span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $selectedAge !== '' ? __(['0–30 hari', '31–60 hari', '61–90 hari', 'Lebih 90 hari'][(int) $selectedAge]) : __('Setakat').' '.$end->format('d/m/Y') }}</span></div>
        <div class="overflow-x-auto"><table class="w-full text-start text-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr>@foreach (['Invois / Pelanggan', 'Jumlah Invois', 'Sudah Dibayar', 'Baki', 'Umur Invois', 'Status', 'Tindakan'] as $label)<th class="px-4 py-3 text-start font-medium">{{ __($label) }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($balances as $row)
                @php(['number' => $number, 'customer' => $customer, 'total' => $total, 'paid' => $paid, 'balance' => $balance, 'days' => $days, 'status' => $status, 'url' => $url] = $row)
                <tr class="text-gray-700 dark:text-gray-300"><td class="px-4 py-4"><p class="font-medium text-gray-900 dark:text-white/90">{{ $number }}</p><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($customer) }}</p></td>@foreach ([$total, $paid, $balance] as $amount)<td class="whitespace-nowrap px-4 py-4 tabular-nums">RM {{ number_format($amount, 2) }}</td>@endforeach<td class="whitespace-nowrap px-4 py-4">{{ $days }} {{ __('hari') }}</td><td class="px-4 py-4"><span class="text-theme-xs font-medium text-brand-600 dark:text-brand-400">{{ __($status) }}</span></td><td class="px-4 py-4"><a href="{{ $url }}" class="document-action">{{ __('Status') }}</a></td></tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada invois untuk pilihan ini.') }}</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="p-4">{{ $balances->links() }}</div>
        <div class="px-4 pb-4"><a class="document-action" href="{{ $dashboardLink() }}#invoice-balances">{{ __('Reset penapis invois') }}</a></div>
    </section>
    @if($panel)
        <section id="dashboard-records" class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 p-5"><h2 class="font-semibold text-gray-900 dark:text-white/90">{{ __(['drafts' => 'Sebut Harga Draf', 'finals' => 'Final Menunggu LO', 'lo' => 'Local Order', 'po' => 'Purchase Order', 'missing-do' => 'LO belum ada DO', 'missing-invoice' => 'LO belum ada invois', 'review' => 'Baucar perlu semakan'][$panel]) }} ({{ $panelRows->total() }})</h2><a href="{{ $dashboardLink() }}" class="document-action">{{ __('Tutup') }}</a></div>
            <table class="w-full text-start text-sm text-gray-700 dark:text-gray-300"><thead class="bg-gray-50 dark:bg-gray-800"><tr><th class="p-4 text-start">{{ __('No. Dokumen') }}</th><th class="p-4 text-start">{{ __('Maklumat') }}</th><th class="p-4 text-start">{{ __('Tindakan') }}</th></tr></thead><tbody>
                @forelse($panelRows as $row)<tr class="border-t border-gray-100 dark:border-gray-800"><td class="p-4">{{ $row['number'] }}</td><td class="p-4">{{ $row['description'] }}</td><td class="p-4"><a href="{{ $row['url'] }}" class="document-action">{{ __('Lihat') }}</a></td></tr>
                @empty<tr><td colspan="3" class="p-6 text-center">{{ __('Tiada rekod untuk pilihan ini.') }}</td></tr>@endforelse
            </tbody></table><div class="p-4">{{ $panelRows->links() }}</div>
        </section>
    @endif
</div>
@endsection
