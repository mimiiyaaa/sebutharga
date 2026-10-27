<div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false" @keydown.escape.window="dropdownOpen = false; $refs.bell.focus()">
    <button x-ref="bell" type="button" @click="dropdownOpen = !dropdownOpen" :aria-expanded="dropdownOpen" aria-controls="payment-notifications" aria-label="{{ __('Notifikasi Bayaran') }}" class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
        <svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @if($count)<span class="absolute -end-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-error-500 px-1 text-theme-xs font-semibold text-white dark:bg-error-500">{{ $count > 99 ? '99+' : $count }}</span>@endif
    </button>
    <div id="payment-notifications" x-cloak x-show="dropdownOpen" x-transition class="absolute -end-16 z-99 mt-4 flex max-h-[480px] w-80 max-w-[calc(100vw-2rem)] flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 sm:end-0 sm:w-96">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
            <div><h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Notifikasi Bayaran') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Invois berbaki atau perlu semakan') }} ({{ $count }})</p></div>
            <button type="button" @click="dropdownOpen = false; $refs.bell.focus()" aria-label="{{ __('Tutup') }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"><svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6" stroke-width="1.7" stroke-linecap="round"/></svg></button>
        </div>
        <ul class="custom-scrollbar min-h-0 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($notifications as $notification)
                <li><a href="{{ route('invoice.customer.payments', $notification->invoice_id) }}" class="block rounded-lg px-2 py-4 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-2"><span class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $notification->invoice_no }}</span><span class="rounded-full bg-warning-50 px-2 py-1 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __($notification->status) }}</span></div>
                    <p class="mt-2 break-words text-sm text-gray-600 dark:text-gray-400">{{ $notification->customer }}</p>
                    <p class="mt-1 text-sm font-medium text-brand-600 dark:text-brand-400">{{ __('Baki') }} RM {{ number_format($notification->balance, 2) }}</p>
                    @if($notification->pending)<p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Amaun baucar belum disahkan') }}</p>@endif
                </a></li>
            @empty
                <li class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __(!empty($unavailable) ? 'Maklumat bayaran belum tersedia.' : 'Tiada bayaran memerlukan perhatian.') }}</li>
            @endforelse
        </ul>
        <a href="{{ route('dashboard') }}#invoice-balances" class="mt-3 block rounded-lg border border-gray-200 px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Lihat Semua Status Bayaran') }}</a>
    </div>
</div>
