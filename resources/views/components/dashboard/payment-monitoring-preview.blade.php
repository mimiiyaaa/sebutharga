@props(['monitoring'])

@php
    $summary = $monitoring['summary'];
    $records = $monitoring['records'];
    $filters = $monitoring['filters'];
    $priorityClasses = [
        'Sudah Lewat' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400',
        'Hampir Jatuh Tempoh' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
        'Perlu Semakan' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-400',
        'Belum Dibayar' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
    ];
@endphp

<section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3 border-s-4 border-brand-500 ps-4">
        <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Pemantauan Bayaran dan Reminder') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ringkasan bayaran pelanggan dan peringatan susulan') }} · {{ __('Dikemas kini') }} {{ $monitoring['updated_at'] }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Belum dibayar', $summary['unpaid'], 'Baki invoice pelanggan', 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'],
            ['Sudah lewat', $summary['overdue'], 'Perlu tindakan segera', 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400'],
            ['Hampir jatuh tempoh', $summary['upcoming'], 'Dalam 7 hari', 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400'],
            ['Perlu semakan', $summary['review'], 'Baucar belum disahkan', 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-400'],
            ['Sudah dibayar', $summary['paid'], 'Baki invoice RM0', 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'],
        ] as [$label, $count, $hint, $colors])
            <div class="rounded-xl p-5 {{ $colors }}">
                <h3 class="text-sm font-medium">{{ __($label) }}</h3>
                <p class="mt-3 text-3xl font-semibold tabular-nums">{{ $count }}</p>
                <p class="mt-2 text-theme-xs">{{ __($hint) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 mt-6 flex w-full flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-900/50">
        <form method="GET" action="{{ route('monitoring') }}" class="flex w-full flex-wrap items-center gap-2">
            <label for="monitoring-search" class="sr-only">{{ __('Kata Kunci') }}</label>
            <input id="monitoring-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Cari invoice atau pelanggan') }}" class="h-11 min-w-48 flex-1 rounded-lg border-0 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-300" />
            <label for="monitoring-priority" class="sr-only">{{ __('Status') }}</label>
            <select id="monitoring-priority" name="priority" class="h-11 w-44 shrink-0 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <option value="">{{ __('Semua status') }}</option>
                @foreach (['Belum Dibayar', 'Sudah Lewat', 'Hampir Jatuh Tempoh', 'Perlu Semakan'] as $priority)
                    <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ __($priority) }}</option>
                @endforeach
            </select>
            <label for="monitoring-from" class="sr-only">{{ __('Tarikh Akhir Dari') }}</label>
            <input id="monitoring-from" type="date" name="from" value="{{ $filters['from'] }}" aria-label="{{ __('Tarikh Akhir Dari') }}" class="h-11 w-40 shrink-0 rounded-lg border-0 bg-white px-3 text-sm text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300" />
            <label for="monitoring-to" class="sr-only">{{ __('Tarikh Akhir Hingga') }}</label>
            <input id="monitoring-to" type="date" name="to" value="{{ $filters['to'] }}" aria-label="{{ __('Tarikh Akhir Hingga') }}" class="h-11 w-40 shrink-0 rounded-lg border-0 bg-white px-3 text-sm text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300" />
            <a href="{{ route('monitoring') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">{{ __('Reset') }}</a>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Cari') }}</button>
        </form>
    </div>
    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white/90">{{ __('Senarai Peringatan') }}</h3>
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
        <table class="min-w-full text-start text-sm">
            <thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400"><tr>
                <th class="px-4 py-3 font-medium">{{ __('Peringatan') }}</th><th class="px-4 py-3 font-medium">{{ __('Invoice') }}</th><th class="px-4 py-3 font-medium">{{ __('Pelanggan') }}</th><th class="px-4 py-3 font-medium">{{ __('Tarikh Akhir') }}</th><th class="px-4 py-3 font-medium">{{ __('Baki') }}</th><th class="px-4 py-3 font-medium">{{ __('Keutamaan') }}</th><th class="px-4 py-3 text-end font-medium">{{ __('Tindakan') }}</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($records as $record)
                    <tr class="text-gray-700 dark:text-gray-300">
                        <td class="px-4 py-3">{{ __($record['reminder']) }}</td><td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white/90">{{ $record['number'] }}</td><td class="whitespace-nowrap px-4 py-3">{{ $record['party'] }}</td><td class="whitespace-nowrap px-4 py-3">{{ $record['due_date'] ? \Carbon\CarbonImmutable::parse($record['due_date'])->format('d/m/Y') : '—' }}</td><td class="whitespace-nowrap px-4 py-3 tabular-nums">RM {{ number_format($record['balance'], 2) }}</td><td class="whitespace-nowrap px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $priorityClasses[$record['priority']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ __($record['priority']) }}</span></td><td class="px-4 py-3 text-end"><a href="{{ $record['url'] }}" class="document-action">{{ __('Lihat') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada peringatan bayaran buat masa ini.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
