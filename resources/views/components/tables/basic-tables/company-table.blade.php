@props(['companies' => collect()])
<div class="overflow-x-auto custom-scrollbar">
    <table class="w-full min-w-[900px]"><thead><tr class="border-b border-gray-100 dark:border-gray-800">
        @foreach (['Nama Syarikat', 'No. Telefon', 'E-mel', 'Person In Charge', 'Alamat', 'Tindakan'] as $heading)<th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ __($heading) }}</p></th>@endforeach
    </tr></thead><tbody>
        @forelse ($companies as $company)
            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $company->nama_syarikat }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->no_telefon ?: '—' }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->emel ?: '—' }}</td><td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->person_in_charge ?: '—' }}</td><td class="max-w-xs px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $company->alamat_syarikat ?: '—' }}</td><td class="whitespace-nowrap px-5 py-4"><div class="flex items-center gap-2"><a href="{{ route('companies.show', $company->company_id) }}" class="inline-flex rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ __('Lihat') }}</a><a href="{{ route('companies.edit', $company->company_id) }}" class="inline-flex rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white">{{ __('Edit') }}</a><form method="POST" action="{{ route('companies.delete', $company->company_id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam rekod ini?' })">@csrf @method('DELETE')<button type="submit" class="inline-flex rounded-lg border border-error-200 p-2 text-error-600" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}">🗑</button></form></div></td></tr>
        @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500 text-theme-sm dark:text-gray-400">{{ __('Tiada rekod untuk dipaparkan.') }}</td></tr>
        @endforelse
    </tbody></table>
</div>
