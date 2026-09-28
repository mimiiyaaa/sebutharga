@props(['documents' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="mb-4 flex items-center justify-between gap-4 px-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Senarai LO') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Rekod letter order yang telah diterima.') }}</p>
        </div>
    </div>

    <div class="max-w-full overflow-x-auto">
        <table class="w-full">
            <thead class="border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    @foreach (['No. LO', 'Pelanggan / Syarikat', 'Tarikh LO', 'Jumlah (RM)', 'Dokumen'] as $heading)
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
                                <a href="{{ asset('storage/' . ltrim($document->document_file, '/')) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Lihat Dokumen') }}</a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Tiada rekod LO untuk dipaparkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
