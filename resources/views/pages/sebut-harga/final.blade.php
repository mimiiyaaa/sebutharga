@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Final Sebut Harga')" />
<x-common.document-card :title="__('Final Sebut Harga')">
    @if (session('success'))<p class="rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>@endif
    <div class="overflow-x-auto">
        <table class="w-full text-start text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-800"><tr>
                @foreach (['No. Sebut Harga', 'Pelanggan', 'Draf', 'Tajuk', 'Tarikh', 'Jumlah (RM)', 'Tindakan'] as $label)
                    <th class="px-4 py-3 text-start font-semibold">{{ __($label) }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse ($finals as $draft)
                    @php($document = App\Helpers\QuotationDocument::data($draft))
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="whitespace-nowrap px-4 py-4 font-medium">{{ $draft->quotation_no }}</td>
                        <td class="px-4 py-4">{{ $document['company_name'] ?? $draft->company_name }}</td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <select aria-label="{{ __('Versi Final') }}" onchange="window.location.href='{{ url('/sebut-harga') }}/{{ $draft->quotation_id }}/edit?draft=' + this.value + '&from=final'" class="h-10 min-w-32 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @foreach ($draft->final_versions as $version)
                                    <option value="{{ $version->quotation_detail_id }}" @selected($version->quotation_detail_id === $draft->quotation_detail_id)>{{ __('Versi') }} {{ $version->final_no ?? $version->draft_no }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-4">{{ $draft->quotation_title }}</td>
                        <td class="whitespace-nowrap px-4 py-4">{{ $draft->quotation_date }}</td>
                        <td class="whitespace-nowrap px-4 py-4 tabular-nums">{{ number_format($draft->jumlah_total, 2) }}</td>
                        <td class="px-4 py-4"><div class="flex items-center gap-2 whitespace-nowrap">
                            <a href="{{ route('sebut-harga.edit', [$draft->quotation_id, 'draft'=>$draft->quotation_detail_id, 'from'=>'final']) }}" class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600">{{ __('Lihat') }}</a>
                            <a href="{{ route('sebut-harga.preview', [$draft->quotation_id, 'draft'=>$draft->quotation_detail_id, 'from'=>'final']) }}" class="rounded-lg border border-brand-200 px-3 py-2 text-brand-600 dark:border-brand-700 dark:text-brand-400">{{ __('Pratonton Dokumen') }}</a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('Tiada sebut harga dimuktamadkan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-common.document-card>
@endsection
