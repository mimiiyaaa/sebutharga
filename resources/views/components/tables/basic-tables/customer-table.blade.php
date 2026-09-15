@props(['contacts' => collect()])

<div class="overflow-x-auto custom-scrollbar">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                @foreach (['Kod', 'Nama', 'Nama Syarikat', 'Alamat', 'Nombor Telefon', 'E-mel', 'No. Rujukan', 'Tindakan'] as $heading)
                    <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ $heading }}</p></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($contacts as $contact)
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $contact->customer_code }}</td>
                    <td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $contact->customer_name ?: '—' }}</td>
                    <td class="px-5 py-4 text-gray-800 text-theme-sm dark:text-white/90">{{ $contact->company_name }}</td>
                    <td class="max-w-xs px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $contact->address }}</td>
                    <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $contact->phone_no ?: '—' }}</td>
                    <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $contact->email ?: '—' }}</td>
                    <td class="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{{ $contact->reference_no ?: '—' }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('contacts.show', ['type' => request()->is('pembekal*') ? 'pembekal' : 'pelanggan', 'id' => $contact->customer_id]) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">{{ __('Lihat') }}</a>
                            <a href="{{ route('contacts.edit', ['type' => request()->is('pembekal*') ? 'pembekal' : 'pelanggan', 'id' => $contact->customer_id]) }}" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('contacts.delete', ['type' => request()->is('pembekal*') ? 'pembekal' : 'pelanggan', 'id' => $contact->customer_id]) }}" onsubmit="return confirm('{{ __('Padam rekod ini?') }}')">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex items-center justify-center rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5h6v2m-7 0 1 13h6l1-13M10 11v5m4-5v5"/></svg></button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500 text-theme-sm dark:text-gray-400">Tiada rekod untuk dipaparkan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
