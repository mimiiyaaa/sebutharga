@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Baucar Bayaran')" />
    <x-common.component-card>
        <div class="min-w-0 max-w-full">
            <table class="document-list-table w-full text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @foreach (['No. Baucar', 'Tarikh', 'Penerima', 'Keterangan', 'Jumlah (RM)', 'Tindakan'] as $heading)
                            <th scope="col" class="p-4 text-start font-semibold text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada baucar bayaran untuk dipaparkan.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-common.component-card>
@endsection
