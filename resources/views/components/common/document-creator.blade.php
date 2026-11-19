<div class="relative" x-data="{ loginInfoOpen: false }" @click.outside="loginInfoOpen = false" @keydown.escape.window="if (loginInfoOpen) { loginInfoOpen = false; $refs.creatorButton.focus(); }">
    <button x-ref="creatorButton" type="button" @click="loginInfoOpen = !loginInfoOpen" :aria-expanded="loginInfoOpen" title="{{ __('Maklumat Login') }}" aria-label="{{ __('Maklumat Login') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-brand-300 hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:text-brand-400">
        <svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    </button>
    <x-common.created-by-panel :name="$creator->name ?? null" :jawatan="$creator->jawatan ?? null" :email="$creator->email ?? null" :created-at="$createdAt" :document-label="$documentLabel" />
</div>
