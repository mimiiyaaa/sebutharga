@props([])

<section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3 border-s-4 border-brand-500 ps-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Pemantauan Bayaran dan Reminder') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ringkasan bayaran pelanggan dan peringatan susulan') }}</p>
        </div>
        <span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Data contoh') }}</span>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Belum dibayar', 8, 'Contoh invois', 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'],
            ['Tertunggak 2 bulan', 3, 'Perlu perhatian', 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400'],
            ['Tertunggak 3 bulan', 2, 'Tindakan segera', 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400'],
            ['Selesai dibayar', 15, 'Contoh invois', 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'],
        ] as [$label, $count, $hint, $colors])
            <div class="rounded-xl p-5 {{ $colors }}">
                <h3 class="text-sm font-medium">{{ __($label) }}</h3>
                <p class="mt-3 text-3xl font-semibold tabular-nums">{{ $count }}</p>
                <p class="mt-2 text-theme-xs">{{ __($hint) }}</p>
            </div>
        @endforeach
    </div>

    <h3 class="mb-3 mt-6 text-sm font-semibold text-gray-900 dark:text-white/90">{{ __('Contoh reminder') }}</h3>
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ([
            ['Invois tertunggak melebihi 3 bulan', 'Membekal Hardware Sdn. Bhd.', '2,500.00', 'Perlu tindakan susulan', 'border-error-500', 'text-error-600 dark:text-error-400'],
            ['Invois tertunggak 2 bulan', 'Maju Jaya Enterprise', '800.00', 'Perlu peringatan bayaran', 'border-warning-500', 'text-warning-700 dark:text-warning-400'],
        ] as [$title, $customer, $amount, $hint, $border, $color])
            <div class="rounded-e-xl border-s-4 bg-gray-50 p-4 dark:bg-gray-800/50 {{ $border }}">
                <p class="text-theme-xs font-semibold {{ $color }}">{{ __($title) }}</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $customer }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Baki') }} RM{{ $amount }} · {{ __($hint) }}</p>
            </div>
        @endforeach
    </div>
    <p class="mt-5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Angka, nama dan tempoh di atas ialah contoh sahaja. Tetapan tempoh sebenar akan ditentukan kemudian.') }}</p>
</section>
