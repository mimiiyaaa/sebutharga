@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Syarikat')" />

    <x-common.component-card :title="__('Senarai Syarikat')">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        @foreach (['Nama Syarikat', 'No. Telefon', 'E-mel', 'Person In Charge'] as $heading)
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ __($heading) }}</p></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $company->nama_syarikat }}</td>
                            <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->no_telefon ?: '—' }}</td>
                            <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->emel ?: '—' }}</td>
                            <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->person_in_charge ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500 text-theme-sm dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
@endsection
