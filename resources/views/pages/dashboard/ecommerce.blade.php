@extends('layouts.app')

@section('content')
@php
    // Presentation-only fixtures. No document or payment queries are made here.
    $metrics = [
        ['Sebut Harga Draf', '12', 'Belum dimuktamadkan', 'Draf'],
        ['Final Menunggu LO', '8', 'Menunggu pesanan pelanggan', 'Final'],
        ['Local Order', '18', 'Pesanan daripada pelanggan', 'LO'],
        ['Purchase Order', '15', 'Pesanan kepada pembekal', 'PO'],
    ];
    $months = ['Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep'];
    $invoiced = [18, 24, 27, 30, 36, 45];
    $received = [12, 16, 20, 22, 25, 31];
    $aging = [['0–30 hari', 20000, 280], ['31–60 hari', 16000, 224], ['61–90 hari', 10000, 140], ['Lebih 90 hari', 8000, 112]];
    $balances = [
        ['INV-CONTOH-001', 'Pelanggan Contoh A', 15000, 7000, 8000, 129, 'Bayaran Sebahagian'],
        ['INV-CONTOH-002', 'Pelanggan Contoh B', 4000, 0, 4000, 75, 'Belum Dibayar'],
        ['INV-CONTOH-003', 'Pelanggan Contoh C', 20000, 14000, 6000, 42, 'Bayaran Sebahagian'],
        ['INV-CONTOH-004', 'Pelanggan Contoh D', 25000, 15000, 10000, 18, 'Bayaran Sebahagian'],
    ];
@endphp
<div class="space-y-6 font-outfit" x-data="{ notice: '' }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white/90">{{ __('Dashboard Operasi & Kewangan') }}</h1>
        <span class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-theme-xs font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Pratonton reka bentuk · Data contoh') }}</span>
    </div>
    <section class="relative isolate overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="pointer-events-none absolute inset-y-0 end-0 w-1/2 bg-gradient-to-t from-brand-50 to-white dark:from-brand-500/10 dark:to-gray-900" aria-hidden="true"></div>
        <div class="relative grid items-center gap-4 px-6 py-7 sm:grid-cols-[minmax(0,1fr)_240px] sm:px-8 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-widest text-brand-600 dark:text-brand-400">K1 Resources</p>
                <h2 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-gray-900 dark:text-white/90 lg:text-4xl">{{ __('Selamat datang kembali') }}!</h2>
                <p class="mt-4 max-w-lg text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Pantau urusan dokumen, penerimaan pelanggan dan baki invois dalam satu paparan.') }}</p>
                <div class="mt-5 flex flex-wrap gap-2 text-theme-xs text-gray-600 dark:text-gray-400">
                    <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">{{ __('Tempoh contoh: April – September 2026') }}</span>
                    <span class="rounded-lg bg-brand-50 px-3 py-2 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Baki contoh setakat 30 September 2026') }}</span>
                </div>
            </div>
            <img src="{{ asset('images/dashboard-mascot.png') }}" alt="{{ __('Maskot dashboard') }}" class="hidden h-64 w-full object-contain drop-shadow-sm sm:block lg:h-72">
        </div>
    </section>

    <section class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="min-w-48 flex-1">
            <label for="dashboard-search" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Carian dokumen') }}</label>
            <input id="dashboard-search" disabled placeholder="{{ __('No. dokumen, pelanggan atau keterangan item') }}" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
        </div>
        <div><label for="dashboard-period" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Tempoh paparan') }}</label><select id="dashboard-period" disabled class="h-10 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><option>{{ __('April – September 2026') }}</option></select></div>
        <p class="w-full text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Carian dan penapis ialah contoh paparan; belum diaktifkan.') }}</p>
    </section>

    @php
        $metricThemes = [
            ['border-warning-200 bg-warning-50 dark:border-warning-900 dark:bg-warning-500/10', 'bg-warning-500 text-white', 'text-warning-700 dark:text-warning-400', 'M8 5h10a2 2 0 0 1 2 2v13H6V7a2 2 0 0 1 2-2Zm2-2v4m6-4v4M9 11h8m-8 4h5'],
            ['border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-500/10', 'bg-brand-500 text-white', 'text-brand-700 dark:text-brand-300', 'M14 3H6v18h12V7l-4-4Zm0 0v5h5M8 14l3 3 5-6'],
            ['border-success-200 bg-success-50 dark:border-success-900 dark:bg-success-500/10', 'bg-success-600 text-white', 'text-success-700 dark:text-success-400', 'M3 7h18v14H3V7Zm2-4h14v4H5V3Zm4 9h6m-6 4h6'],
            ['border-blue-light-200 bg-blue-light-50 dark:border-blue-light-900 dark:bg-blue-light-500/10', 'bg-blue-light-600 text-white', 'text-blue-light-700 dark:text-blue-light-400', 'M3 3h2l3 12h10l3-8H6m3 13h1m6 0h1'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as [$label, $value, $hint, $tag])
            @php([$surface, $icon, $ink, $path] = $metricThemes[$loop->index])
            <section class="rounded-2xl border p-5 shadow-theme-xs {{ $surface }}">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl shadow-theme-xs {{ $icon }}"><svg class="h-6 w-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="{{ $path }}" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                    <span class="rounded-full bg-white/70 px-3 py-1 text-theme-xs font-semibold dark:bg-gray-900/40 {{ $ink }}">{{ __($tag) }}</span>
                </div>
                <div class="mt-5 flex items-baseline justify-between gap-2"><h2 class="text-sm font-semibold {{ $ink }}">{{ __($label) }}</h2><p class="text-3xl font-semibold tabular-nums text-gray-900 dark:text-white/90">{{ $value }}</p></div>
                <p class="mt-3 border-t border-current/10 pt-3 text-theme-xs {{ $ink }}">{{ __($hint) }}</p>
            </section>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-5 dark:border-gray-800"><h2 class="border-s-4 border-brand-500 ps-3 text-base font-semibold text-gray-900 dark:border-brand-400 dark:text-white/90">{{ __('Kedudukan Bayaran Pelanggan') }}</h2><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Berdasarkan invois kepada pelanggan') }}</span></div>
        <div class="grid gap-5 p-6 md:grid-cols-3">
            @foreach ([['Jumlah Invois', '180,000.00', '24 invois pelanggan'], ['Bayaran Diterima', '126,000.00', 'Daripada baucar pelanggan'], ['Baki Belum Dibayar', '54,000.00', 'Termasuk bayaran sebahagian']] as [$label, $amount, $hint])
                <div class="rounded-xl bg-gray-50 p-5 dark:bg-gray-800/50"><p class="text-sm text-gray-600 dark:text-gray-400">{{ __($label) }}</p><p @class(['mt-2 text-2xl font-semibold tabular-nums', 'text-gray-900 dark:text-white/90' => $loop->first, 'text-success-600 dark:text-success-400' => $loop->iteration === 2, 'text-warning-600 dark:text-warning-400' => $loop->last])><span class="text-sm font-medium">RM</span> {{ $amount }}</p><p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($hint) }}</p></div>
            @endforeach
        </div>
    </section>

    <div class="grid items-stretch gap-6 xl:grid-cols-3">
        <section class="min-w-0 rounded-2xl border border-gray-200 bg-white p-6 xl:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Trend Invois & Penerimaan') }}</h2>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Perbandingan bulanan · RM ribu') }}</p>
            <div class="mt-4 flex flex-wrap gap-4 text-theme-xs text-gray-600 dark:text-gray-400"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-brand-500"></span>{{ __('Invois Dikeluarkan') }}</span><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-success-500"></span>{{ __('Bayaran Diterima') }}</span></div>
            <svg viewBox="0 0 620 260" class="mt-4 w-full" role="img" aria-labelledby="trend-chart-title">
                <title id="trend-chart-title">{{ __('Carta contoh nilai invois dan bayaran diterima dari April hingga September 2026.') }}</title>
                @foreach ([0, 10, 20, 30, 40, 50] as $tick)
                    <line x1="40" x2="605" y1="{{ 220 - $tick * 4 }}" y2="{{ 220 - $tick * 4 }}" class="stroke-gray-100 dark:stroke-gray-800" />
                    <text x="30" y="{{ 224 - $tick * 4 }}" text-anchor="end" class="fill-gray-400 text-theme-xs dark:fill-gray-500">{{ $tick }}</text>
                @endforeach
                @foreach ($months as $i => $month)
                    <rect x="{{ 62 + $i * 91 }}" y="{{ 220 - $invoiced[$i] * 4 }}" width="24" height="{{ $invoiced[$i] * 4 }}" rx="4" class="fill-brand-500"><title>{{ __($month) }}: {{ __('Invois') }} RM {{ number_format($invoiced[$i] * 1000) }}</title></rect>
                    <rect x="{{ 91 + $i * 91 }}" y="{{ 220 - $received[$i] * 4 }}" width="24" height="{{ $received[$i] * 4 }}" rx="4" class="fill-success-500"><title>{{ __($month) }}: {{ __('Bayaran Diterima') }} RM {{ number_format($received[$i] * 1000) }}</title></rect>
                    <text x="{{ 88 + $i * 91 }}" y="247" text-anchor="middle" class="fill-gray-500 text-theme-xs dark:fill-gray-400">{{ __($month) }}</text>
                @endforeach
            </svg>
            <p class="mt-2 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Bayaran diterima pada sesuatu bulan boleh merujuk kepada invois bulan terdahulu.') }}</p>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Status Invois Pelanggan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Agihan 24 invois contoh') }}</p>
            <div class="relative mx-auto my-5 h-40 w-40">
                <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90" role="img" aria-label="{{ __('10 selesai, 6 bayaran sebahagian, 8 belum dibayar') }}"><circle cx="60" cy="60" r="48" fill="none" stroke-width="13" pathLength="24" class="stroke-gray-100 dark:stroke-gray-800"/><circle cx="60" cy="60" r="48" fill="none" stroke-width="13" pathLength="24" stroke-dasharray="10 14" class="stroke-success-500"/><circle cx="60" cy="60" r="48" fill="none" stroke-width="13" pathLength="24" stroke-dasharray="6 18" stroke-dashoffset="-10" class="stroke-brand-500"/><circle cx="60" cy="60" r="48" fill="none" stroke-width="13" pathLength="24" stroke-dasharray="8 16" stroke-dashoffset="-16" class="stroke-warning-400"/></svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center"><span class="text-3xl font-semibold text-gray-900 dark:text-white/90">24</span><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Invois') }}</span></div>
            </div>
            <dl class="space-y-3 text-sm">
                @foreach ([['Selesai Dibayar', 10, 'bg-success-500'], ['Bayaran Sebahagian', 6, 'bg-brand-500'], ['Belum Dibayar', 8, 'bg-warning-400']] as [$label, $count, $color])
                    <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span><dt class="flex-1 text-gray-600 dark:text-gray-400">{{ __($label) }}</dt><dd class="font-semibold text-gray-900 dark:text-white/90">{{ $count }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Aging Baki Invois') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Umur dikira daripada tarikh invois, bukan tarikh lewat bayar.') }}</p>
            <div class="mt-6 space-y-5">
                @foreach ($aging as [$label, $amount, $width])
                    <div><div class="mb-2 flex justify-between gap-3 text-sm"><span class="text-gray-600 dark:text-gray-400">{{ __($label) }}</span><span class="font-semibold tabular-nums text-gray-900 dark:text-white/90">RM {{ number_format($amount, 2) }}</span></div><svg viewBox="0 0 280 8" class="h-2 w-full" preserveAspectRatio="none" role="img" aria-label="{{ __($label) }}: RM {{ number_format($amount, 2) }}"><rect width="280" height="8" rx="4" class="fill-gray-100 dark:fill-gray-800"/><rect width="{{ $width }}" height="8" rx="4" @class(['fill-brand-500' => !$loop->last, 'fill-warning-500' => $loop->last]) /></svg></div>
                @endforeach
            </div>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Perlu Tindakan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Senarai semakan urusan yang belum lengkap') }}</p>
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ([['Final menunggu LO', 8, 'Susuli pesanan pelanggan'], ['LO belum ada DO', 4, 'Semak dokumen penghantaran'], ['LO belum ada invois', 3, 'Semak invois pelanggan'], ['Baucar perlu semakan', 2, 'Sahkan amaun bayaran lama']] as [$label, $count, $hint])
                    <div class="flex items-center gap-4 py-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ $count }}</span><div class="min-w-0 flex-1"><h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __($label) }}</h3><p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($hint) }}</p></div><button type="button" @click="notice = @js(__('Ini data contoh. Pautan perincian akan disambungkan kemudian.'))" class="document-action">{{ __('Lihat') }}</button></div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5"><div><h2 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ __('Invois Berbaki — Keutamaan Susulan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Contoh empat invois pilihan, disusun mengikut umur tertinggi') }}</p></div><span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Data contoh') }}</span></div>
        <div class="overflow-x-auto"><table class="w-full text-start text-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr>@foreach (['Invois / Pelanggan', 'Jumlah Invois', 'Sudah Dibayar', 'Baki', 'Umur Invois', 'Status', 'Tindakan'] as $label)<th class="px-4 py-3 text-start font-medium">{{ __($label) }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($balances as [$number, $customer, $total, $paid, $balance, $days, $status])
                <tr class="text-gray-700 dark:text-gray-300"><td class="px-4 py-4"><p class="font-medium text-gray-900 dark:text-white/90">{{ $number }}</p><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __($customer) }}</p></td>@foreach ([$total, $paid, $balance] as $amount)<td class="whitespace-nowrap px-4 py-4 tabular-nums">RM {{ number_format($amount, 2) }}</td>@endforeach<td class="whitespace-nowrap px-4 py-4">{{ $days }} {{ __('hari') }}</td><td class="px-4 py-4"><span class="text-theme-xs font-medium text-brand-600 dark:text-brand-400">{{ __($status) }}</span></td><td class="px-4 py-4"><button type="button" class="document-action" @click="notice = @js(__('Ini data contoh. Pautan perincian akan disambungkan kemudian.'))">{{ __('Status') }}</button></td></tr>
            @endforeach
        </tbody></table></div>
    </section>
    <div x-cloak x-show="notice" role="status" class="flex items-center justify-between gap-4 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-700 dark:border-brand-800 dark:bg-brand-500/10 dark:text-brand-300"><p x-text="notice"></p><button @click="notice = ''" type="button" class="font-medium">{{ __('Tutup') }}</button></div>
    <p class="pb-2 text-center text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Semua angka dan rekod di halaman ini ialah contoh susun atur, bukan data sebenar syarikat.') }}</p>
</div>
@endsection
