@extends('layouts.app')

@section('content')
  <p class="mb-5 text-3xl font-semibold tracking-tight text-gray-800 dark:text-white/90">{{ __('Dashboard') }}</p>

  <div class="grid grid-cols-12 items-start gap-5 md:gap-6">
    <div class="col-span-12 space-y-5 xl:col-span-8">
    <section class="relative isolate h-[300px] overflow-visible rounded-3xl border border-gray-200 bg-white px-6 py-8 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:px-10">
      <div class="grid h-full grid-cols-1 sm:grid-cols-[minmax(0,1fr)_350px]">
        <div class="relative z-10 self-center">
          <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white/90 sm:text-4xl">
            {{ __('Selamat datang kembali') }},<br>
            <span class="text-brand-600 dark:text-brand-400">{{ auth()->user()?->name ?? __('Pengguna') }}!</span>
          </h1>
          <p class="mt-4 max-w-md text-sm leading-6 text-gray-600 dark:text-gray-300">
            {{ __('Semak ringkasan sebut harga dan urus rekod syarikat anda di sini.') }}
          </p>
        </div>
        <div class="relative hidden sm:block">
          <img src="{{ asset('images/dashboard-mascot.png') }}" alt="{{ __('Maskot dashboard') }}" class="pointer-events-none absolute bottom-[-50px] end-0 z-20 h-[380px] w-[380px] max-w-none object-contain drop-shadow-sm" />
        </div>
      </div>
    </section>

    <div class="col-span-12">
      <x-ecommerce.ecommerce-metrics />
    </div>

    <div class="col-span-12 xl:col-span-7">
      <x-ecommerce.monthly-sale />
    </div>

    </div>

    <div class="col-span-12 space-y-5 xl:col-span-4">
      <x-ecommerce.monthly-target />
      <x-ecommerce.customer-demographic />
    </div>

    <div class="col-span-12">
      <x-ecommerce.statistics-chart />
    </div>

    <div class="col-span-12">
      <x-ecommerce.recent-orders />
    </div>
  </div>
@endsection
