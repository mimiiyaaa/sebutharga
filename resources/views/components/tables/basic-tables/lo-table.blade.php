@props(['documents' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="mb-4 flex flex-col gap-4 px-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Senarai Local Order') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Rekod letter order yang telah diterima.') }}</p>
        </div>
        <a href="{{ route('lo.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white transition hover:bg-brand-600">{{ __('+ Tambah Local Order') }}</a>
    </div>

    <div class="max-w-full overflow-x-auto">
        <table class="w-full">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    @foreach (['No. LO', 'Pelanggan / Syarikat', 'Tarikh LO', 'Jumlah (RM)', 'Dokumen', 'Tindakan'] as $heading)
                        <th scope="col" class="whitespace-nowrap px-6 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __($heading) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr class="border-b border-gray-100 font-outfit text-theme-sm text-gray-600 dark:border-white/[0.05] dark:text-gray-300">
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-800 dark:text-white/90">{{ $document->lo_inden_no }}</td>
                        <td class="min-w-60 px-6 py-4 leading-relaxed">{{ $document->company_name ?: ($document->customer_name ?: '—') }}</td>
                        <td class="whitespace-nowrap px-6 py-4">{{ \Carbon\Carbon::parse($document->lo_inden_date)->format('d/m/Y') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-medium tabular-nums">{{ number_format((float) $document->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($document->document_file)
                                <a href="{{ route('lo.document', $document->lo_inden_id) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Lihat Dokumen') }}</a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4"><div class="flex items-center gap-2"><a href="{{ route('lo.show', $document->lo_inden_id) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Lihat') }}</a><a href="{{ route('lo.edit', $document->lo_inden_id) }}" class="rounded-lg bg-brand-500 px-3 py-2 text-theme-xs font-medium text-white transition hover:bg-brand-600">{{ __('Edit') }}</a><form method="POST" action="{{ route('lo.delete', $document->lo_inden_id) }}" @submit.prevent="$dispatch('confirm-action', { form: $el, message: 'Padam LO ini?' })">@csrf @method('DELETE')<button type="submit" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}" class="inline-flex rounded-lg border border-error-200 p-2 text-error-600 transition hover:bg-error-50 dark:border-error-700 dark:text-error-400 dark:hover:bg-error-500/10"><svg class="h-4 w-4 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5m4-5v5"/></svg></button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Tiada rekod LO untuk dipaparkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
