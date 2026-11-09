@props(['number', 'pdfUrl', 'downloadUrl', 'label' => __('Pratonton PDF')])
<section x-data="{ ready: false, printHelp: false, printDocument() { try { this.$refs.pdf.contentWindow.focus(); this.$refs.pdf.contentWindow.print(); } catch (error) { this.printHelp = true; } } }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 p-5 dark:border-gray-800">
        <div class="border-s-4 border-brand-500 ps-4">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white/90">{{ $number }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Dokumen PDF sebenar · Format A4') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="printDocument()" :disabled="!ready" class="document-action document-action-primary disabled:cursor-wait disabled:opacity-50">
                <svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 8V3h10v5M7 17H4V8h16v9h-3M7 14h10v7H7zM17 11h.01" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('Cetak') }}
            </button>
            <a href="{{ $downloadUrl }}" class="document-action">{{ __('Muat Turun PDF') }}</a>
        </div>
    </div>
    <p class="border-b border-gray-200 px-5 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Semak dan cetak dokumen di sini. Halaman sistem kekal terbuka selepas cetakan.') }}</p>
    <p x-cloak x-show="printHelp" role="status" class="bg-warning-50 px-5 py-3 text-sm text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Gunakan ikon cetak pada bar alat PDF di bawah jika pelayar menyekat butang Cetak.') }}</p>
    <iframe x-ref="pdf" @load="ready = true" title="{{ __('Pratonton PDF') }} — {{ $number }}" src="{{ $pdfUrl }}#zoom=60" class="block h-[75vh] min-h-[560px] w-full border-0 bg-gray-100 dark:bg-gray-800"></iframe>
</section>
