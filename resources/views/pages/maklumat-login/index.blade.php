@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Maklumat Login')" />
<x-common.component-card :title="__('Senarai Pengguna')">
    @if (session('success'))<p class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>@endif
    <a href="{{ route('maklumat-login.create') }}" class="mb-4 inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">+ {{ __('Tambah Pengguna') }}</a>
    <div class="overflow-x-auto custom-scrollbar"><table class="w-full min-w-[850px]"><thead><tr class="border-b border-gray-100 dark:border-gray-800">@foreach (['Nama', 'No. Kad Pengenalan', 'E-mel', 'Tarikh Daftar', 'Tindakan'] as $heading)<th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>@endforeach</tr></thead><tbody>
        @forelse ($users as $user)
            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="px-5 py-4 font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $user->name }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->no_kp ?: '—' }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->email }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->created_at ? date('d/m/Y', strtotime($user->created_at)) : '—' }}</td><td class="whitespace-nowrap px-5 py-4"><div class="flex items-center gap-2"><a href="{{ route('maklumat-login.edit', $user->id) }}" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white">{{ __('Edit') }}</a><form method="POST" action="{{ route('maklumat-login.delete', $user->id) }}" onsubmit="return confirm('{{ __('Padam pengguna ini?') }}')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 p-2 text-error-600 dark:border-error-700 dark:text-error-400" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}">🗑</button></form></div></td></tr>
        @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada pengguna berdaftar.') }}</td></tr>
        @endforelse
    </tbody></table></div>
</x-common.component-card>
@endsection
