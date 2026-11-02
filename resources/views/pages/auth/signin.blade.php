@extends('layouts.fullscreen-layout')

@section('content')
<main class="relative flex min-h-screen flex-col bg-white font-outfit text-gray-900 dark:bg-gray-950 dark:text-white/90">
    <header class="absolute end-0 top-0 z-10 flex items-center justify-end px-6 py-5 sm:px-8">
        <button type="button" @click="$store.theme.toggle()" aria-label="{{ __('Tukar tema paparan') }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
            <svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 14A8 8 0 0 1 10 4a8 8 0 1 0 10 10Z" stroke-width="1.7" stroke-linejoin="round"/></svg>
        </button>
    </header>
    <div class="flex flex-1">
        <div class="grid min-h-screen w-full bg-white lg:grid-cols-2 dark:bg-gray-900">
            <section class="relative flex flex-col justify-between overflow-hidden bg-error-950 px-8 pb-8 pt-20 text-white sm:px-12 lg:p-16 xl:p-20 dark:bg-error-950">
                <div aria-hidden="true" class="pointer-events-none absolute -end-24 -bottom-32 h-96 w-96 rounded-full border border-white/10"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -end-8 -bottom-16 h-64 w-64 rounded-full border border-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <span class="inline-flex h-24 w-24 shrink-0 overflow-hidden rounded-full sm:h-28 sm:w-28"><x-common.brand-logo class="h-full w-full" /></span>
                    <div><p class="text-base font-semibold tracking-wide">K ONE Resources</p><p class="mt-1 text-xs text-white/60">{{ __('Sistem Sebut Harga & Invoice') }}</p></div>
                </div>
                <div class="relative my-8 lg:my-16">
                    <span class="mb-5 block h-1 w-10 rounded-full bg-white/50"></span>
                    <h1 class="max-w-sm text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ __('Urus dokumen.') }}<br>{{ __('Pantau bayaran.') }}</h1>
                    <p class="mt-5 max-w-xs text-sm leading-6 text-white/70">{{ __('Sebut harga, pesanan dan bayaran dalam satu sistem yang tersusun.') }}</p>
                </div>
                <div class="relative hidden items-center gap-3 border-t border-white/15 pt-5 text-xs text-white/60 lg:flex">
                    <span>{{ __('Sebut Harga') }}</span><span aria-hidden="true">/</span><span>{{ __('Pesanan') }}</span><span aria-hidden="true">/</span><span>{{ __('Bayaran') }}</span>
                </div>
            </section>
            <section class="flex items-center px-7 py-12 sm:px-12 lg:px-16 lg:py-20">
                <div class="mx-auto w-full max-w-md">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-error-800 dark:text-error-300">{{ __('Selamat kembali') }}</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90">{{ __('Log Masuk') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Masukkan butiran akaun untuk meneruskan.') }}</p>
                    <form action="{{ route('signin.store') }}" method="POST" class="mt-8 space-y-5">
                        @csrf
                        @if($errors->any())
                            <div id="login-error" role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-800 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first() }}</div>
                        @endif
                        <div>
                            <label for="no_kp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('No. Kad Pengenalan') }}</label>
                            <input type="text" id="no_kp" name="no_kp" value="{{ old('no_kp') }}" required autocomplete="username" placeholder="{{ __('Masukkan no. kad pengenalan') }}" @if($errors->any()) aria-describedby="login-error" aria-invalid="true" @endif class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-error-700 focus:ring-3 focus:ring-error-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white/90 dark:focus:border-error-400">
                        </div>
                        <div x-data="{ showPassword: false }">
                            <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kata Laluan') }}</label>
                            <div class="relative">
                                <input type="password" :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password" placeholder="{{ __('Masukkan kata laluan anda') }}" class="h-12 w-full rounded-xl border border-gray-300 bg-white ps-4 pe-12 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-error-700 focus:ring-3 focus:ring-error-500/10 dark:border-gray-700 dark:bg-gray-950 dark:text-white/90 dark:focus:border-error-400">
                                <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? @js(__('Sembunyikan kata laluan')) : @js(__('Tunjukkan kata laluan'))" :aria-pressed="showPassword" class="absolute inset-y-0 end-0 flex w-12 items-center justify-center rounded-e-xl text-gray-400 hover:text-gray-700 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-gray-500 dark:hover:text-gray-200">
                                    <svg class="h-5 w-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke-width="1.6"/><path x-show="showPassword" x-cloak d="m3 3 18 18" stroke-width="1.6"/></svg>
                                </button>
                            </div>
                        </div>
                        <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-gray-300 accent-error-800 dark:border-gray-600">
                            {{ __('Kekalkan log masuk') }}
                        </label>
                        <button type="submit" class="flex h-12 w-full items-center justify-center gap-3 rounded-xl bg-error-800 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-error-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-error-700 dark:bg-error-700 dark:hover:bg-error-800">
                            {{ __('Log Masuk') }}<svg class="h-4 w-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                    <p class="mt-7 border-t border-gray-100 pt-5 text-xs leading-5 text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Perlukan bantuan akses? Hubungi pentadbir sistem.') }}</p>
                    <p class="mt-8 text-xs text-gray-400 dark:text-gray-500">&copy; {{ now()->year }} K1R Resources</p>
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
