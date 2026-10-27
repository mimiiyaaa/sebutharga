@extends('layouts.app')

@section('content')
    @php
        $highlight = static function ($value) use ($term) {
            $value = e($value ?? '-');
            $needle = e($term);

            if ($needle === '') {
                return $value;
            }

            return preg_replace(
                '/' . preg_quote($needle, '/') . '/iu',
                '<mark class="rounded bg-yellow-100 px-0.5 text-yellow-900 dark:bg-yellow-500/30 dark:text-yellow-100">$0</mark>',
                $value
            ) ?: $value;
        };
    @endphp

    <x-common.page-breadcrumb pageTitle="Carian Global" />

    <div class="space-y-6">
        <x-common.component-card title="Cari dalam semua modul" desc="Cari nombor dokumen, nama pelanggan atau pembekal, tajuk, item dan status.">
            <form method="GET" action="{{ route('global-search') }}" class="flex flex-col gap-3 sm:flex-row">
                <label for="global-search-input" class="sr-only">Carian</label>
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 ltr:left-4 rtl:right-4 text-gray-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.55" stroke="currentColor" stroke-width="1.6"/><path d="m16 16 4.25 4.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </span>
                    <input id="global-search-input" name="q" value="{{ $term }}" autofocus placeholder="Contoh: membekal, ABC Sdn Bhd atau INV-001" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 ltr:pl-12 ltr:pr-4 rtl:pr-12 rtl:pl-4 text-sm text-gray-800 shadow-theme-xs outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Cari</button>
            </form>
        </x-common.component-card>

        @if ($term !== '')
            <x-common.component-card title="Keputusan carian" :desc="$results->count() . ' rekod dijumpai untuk “' . $term . '”'">
                @if ($results->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 px-5 py-10 text-center dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Tiada rekod dijumpai.</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cuba kata kunci lain atau gunakan nombor dokumen.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-start text-xs">
                            <thead><tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">Jenis</th>
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">Dokumen</th>
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">Pelanggan / Pembekal</th>
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">Tarikh</th>
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">Status</th>
                                <th class="px-3 py-2.5 text-[11px] font-medium uppercase text-gray-500">REV / INDENT</th>
                                <th class="px-3 py-2.5 text-end text-[11px] font-medium uppercase text-gray-500">Tindakan</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($results as $result)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400">{!! $highlight($result['type']) !!}</td>
                                        <td class="px-3 py-2.5"><p class="text-sm font-medium text-gray-800 dark:text-white/90">{!! $highlight($result['reference']) !!}</p><p class="mt-0.5 max-w-sm text-[11px] text-gray-500 dark:text-gray-400">{!! $highlight($result['subject']) !!}</p></td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-gray-600 dark:text-gray-300">{!! $highlight($result['party']) !!}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $result['date'] ? \Illuminate\Support\Carbon::parse($result['date'])->format('d/m/Y') : '-' }}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-gray-600 dark:text-gray-300">{!! $highlight($result['status']) !!}</td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-[11px] text-gray-600 dark:text-gray-300">
                                            @if ($result['rev'] !== null || $result['indent'] !== null)
                                                <div class="flex items-center gap-1.5"><span class="min-w-10">REV</span><span class="inline-flex min-w-5 justify-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $result['rev'] ?? 0 }}</span></div>
                                                <div class="mt-1 flex items-center gap-1.5"><span class="min-w-10">INDENT</span><span class="inline-flex min-w-5 justify-center rounded px-1.5 py-0.5 text-[10px] font-medium {{ ($result['indent'] ?? 0) > 0 ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-700 dark:bg-white/[0.06] dark:text-gray-300' }}">{{ $result['indent'] ?? 0 }}</span></div>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-end"><a href="{{ $result['url'] }}" aria-label="Lihat {{ $result['reference'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-white transition hover:bg-brand-600 focus:outline-none focus:ring-3 focus:ring-brand-500/20"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12s3.25-5.25 9.25-5.25S21.25 12 21.25 12 18 17.25 12 17.25 2.75 12 2.75 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-width="1.6"/></svg><span class="sr-only">Lihat</span></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-common.component-card>
        @else
            <x-common.component-card title="Cara menggunakan carian">
                <p class="text-sm text-gray-500 dark:text-gray-400">Masukkan kata kunci seperti <span class="font-medium text-gray-800 dark:text-white/90">membekal</span>, nama syarikat, nombor invoice atau nombor PO untuk mencari rekod merentas semua modul.</p>
            </x-common.component-card>
        @endif
    </div>
@endsection
