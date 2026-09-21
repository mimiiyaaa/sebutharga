<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Sebut Harga - Laravel Tailwind CSS Admin Dashboard Template</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Theme Store -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme === 'dark' ? 'dark' : 'light';
                    this.updateTheme();
                },
                theme: 'light',
                resolvedTheme: 'light',
                set(value) {
                    value = value === 'dark' ? 'dark' : 'light';
                    this.theme = value;
                    localStorage.setItem('theme', value);
                    this.updateTheme();
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: value }));
                },
                toggle() {
                    this.set(this.resolvedTheme === 'dark' ? 'light' : 'dark');
                },
                updateTheme() {
                    const html = document.documentElement;
                    const isDark = this.theme === 'dark';
                    if (isDark) {
                        html.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                    }

                    this.resolvedTheme = isDark ? 'dark' : 'light';
                    html.setAttribute('data-color-scheme', this.resolvedTheme);
                    html.dataset['theme'] = this.resolvedTheme;
                    html.style.colorScheme = this.resolvedTheme;
                    if (document.body) {
                        document.body.dataset['theme'] = this.resolvedTheme;
                        document.body.style.colorScheme = this.resolvedTheme;
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: false,
                isMobileOpen: false,
                isHovered: false,

                init() {
                    const savedState = localStorage.getItem('sidebarExpanded');
                    if (window.innerWidth >= 1280) {
                        this.isExpanded = savedState === null ? true : savedState === 'true';
                    } else {
                        this.isExpanded = false;
                    }
                    this.isMobileOpen = false;

                    window.addEventListener('resize', () => {
                        this.handleResize();
                    });
                },

                handleResize() {
                    if (window.innerWidth < 1280) {
                        if (this.isMobileOpen) {
                             this.isMobileOpen = false;
                        }
                    } else {
                        this.isMobileOpen = false;
                        const savedState = localStorage.getItem('sidebarExpanded');
                        this.isExpanded = savedState === null ? true : savedState === 'true';
                    }
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                    
                    if (window.innerWidth >= 1280) {
                        localStorage.setItem('sidebarExpanded', this.isExpanded);
                    }
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply RTL and dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedDir = localStorage.getItem('dir');
            const savedLocale = localStorage.getItem('locale');
            if (savedDir) {
                document.documentElement.setAttribute('dir', savedDir);
            } else if (savedLocale === 'ar') {
                document.documentElement.setAttribute('dir', 'rtl');
            }
            if (savedLocale) {
                document.documentElement.setAttribute('lang', savedLocale);
            }

            const savedTheme = localStorage.getItem('theme');
            const isDark = savedTheme === 'dark';
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-color-scheme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-color-scheme', 'light');
            }
        })();
    </script>
    

</head>

<body>

    @php
        $toastType = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : (session('status') ? 'info' : ($errors->any() ? 'error' : null))));
        $toastMessage = session('success') ?: session('error') ?: session('warning') ?: session('status') ?: ($errors->first() ?: null);
    @endphp
    @if ($toastMessage)
        <div x-data="{ visible: true }" x-show="visible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="translate-y-2 opacity-0" x-init="setTimeout(() => visible = false, 4500)" class="fixed top-6 z-999999 w-[calc(100vw-3rem)] max-w-md ltr:right-6 rtl:right-6" role="alert">
            <div class="flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-theme-lg dark:bg-gray-900 {{ $toastType === 'success' ? 'border-success-200 dark:border-success-500/30' : ($toastType === 'error' ? 'border-error-200 dark:border-error-500/30' : ($toastType === 'warning' ? 'border-warning-200 dark:border-warning-500/30' : 'border-brand-200 dark:border-brand-500/30')) }}">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $toastType === 'success' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : ($toastType === 'error' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : ($toastType === 'warning' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400')) }}">
                    @if ($toastType === 'success')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                    @elseif ($toastType === 'error')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86a2 2 0 0 0 1.73-3L13.73 4a2 2 0 0 0-3.46 0L3.34 16a2 2 0 0 0 1.73 3Z"/></svg>
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    @endif
                </div>
                <p class="flex-1 pt-1 text-sm font-medium leading-6 {{ $toastType === 'success' ? 'text-success-700 dark:text-success-400' : ($toastType === 'error' ? 'text-error-700 dark:text-error-400' : ($toastType === 'warning' ? 'text-warning-700 dark:text-warning-400' : 'text-brand-700 dark:text-brand-400')) }}">{{ $toastMessage }}</p>
                <button type="button" @click="visible = false" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200" aria-label="Tutup">&times;</button>
            </div>
        </div>
    @endif

    <div class="min-h-screen xl:flex sidebar-expanded" x-data="{ actionModalOpen: false, actionForm: null, actionMessage: '', actionButton: 'Padam' }" @confirm-action.window="actionForm = $event.detail.form; actionMessage = $event.detail.message; actionButton = $event.detail.button || 'Padam'; actionModalOpen = true" :class="{ 'sidebar-expanded': $store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen }">
        <div x-cloak x-show="actionModalOpen" x-transition.opacity class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 px-4" @keydown.escape.window="actionModalOpen = false">
            <div x-show="actionModalOpen" x-transition class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900" @click.outside="actionModalOpen = false">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 4h.01M5.07 19h13.86a2 2 0 0 0 1.73-3L13.73 4a2 2 0 0 0-3.46 0L3.34 16a2 2 0 0 0 1.73 3Z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sahkan tindakan</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400" x-text="actionMessage"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="actionModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Batal</button>
                    <button type="button" @click="actionModalOpen = false; actionForm.submit()" class="inline-flex min-w-24 items-center justify-center rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-error-600" x-text="actionButton"></button>
                </div>
            </div>
        </div>
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        {{-- transition-all duration-300 ease-in-out --}}
        <div class="flex-1 ml-0 ltr:xl:ml-[90px] rtl:xl:ml-0 rtl:xl:mr-[90px] [.sidebar-expanded_&]:ltr:xl:ml-[290px] [.sidebar-expanded_&]:rtl:xl:ml-0 [.sidebar-expanded_&]:rtl:xl:mr-[290px] transition-all duration-300 ease-in-out">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

</body>

@stack('scripts')

</html>
