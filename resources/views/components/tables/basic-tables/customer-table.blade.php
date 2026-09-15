@props(['contacts' => collect()])

<div class="overflow-x-auto custom-scrollbar">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                @foreach (['Kod', 'Nama', 'Nama Syarikat', 'Alamat', 'Nombor Telefon', 'E-mel', 'No. Rujukan'] as $heading)
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
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500 text-theme-sm dark:text-gray-400">Tiada rekod untuk dipaparkan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
