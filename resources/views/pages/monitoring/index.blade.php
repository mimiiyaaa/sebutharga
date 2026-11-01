@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb :pageTitle="__('Pemantauan')" />
<div class="space-y-6">
    <div class="border-s-4 border-brand-500 ps-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white/90">{{ __('Pemantauan Dokumen dan Bayaran') }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Status pesanan, jualan, pembelian dan baki bayaran') }} | {{ __('Dikemas kini') }} {{ $monitoring['updated_at'] }}</p>
    </div>
    @if(session('success'))<p class="rounded-xl bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</p>@endif
    <nav class="flex flex-wrap gap-2" aria-label="{{ __('Bahagian pemantauan') }}">
        @foreach(['lo' => 'Status LO', 'customer' => 'Bayaran Pelanggan', 'supplier' => 'Bayaran Pembekal'] as $key => $label)
        <a href="{{ route('monitoring', ['tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif class="rounded-xl border px-5 py-3 text-sm font-medium {{ $tab === $key ? 'border-brand-500 bg-brand-500 text-white dark:border-brand-400 dark:bg-brand-500' : 'border-gray-200 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">{{ __($label) }}</a>
        @endforeach
    </nav>
    <form method="GET" action="{{ route('monitoring') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="min-w-48 flex-1 text-sm text-gray-700 dark:text-gray-300">{{ __('Carian') }}<input name="q" value="{{ $monitoring['filters']['q'] }}" placeholder="{{ __('No. dokumen atau nama syarikat') }}" class="mt-1 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-3 dark:border-gray-700"></label>
        <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Status') }}<select name="priority" class="mt-1 block h-11 rounded-lg border border-gray-200 bg-white px-3 dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('Semua status') }}</option>
            @foreach(($tab === 'lo' ? ['complete' => 'Dokumen Lengkap', 'missing-po' => 'Belum Ada PO', 'missing-do' => 'Belum Ada DO', 'missing-invoice' => 'Belum Ada Invois'] : array_combine(['Belum Dibayar','Bayaran Sebahagian','Selesai Dibayar','Perlu Semakan'], ['Belum Dibayar','Bayaran Sebahagian','Selesai Dibayar','Perlu Semakan']) + ($tab === 'customer' ? ['Sudah Lewat' => 'Sudah Lewat', 'Hampir Jatuh Tempoh' => 'Hampir Jatuh Tempoh'] : [])) as $key => $label)
            <option value="{{ $key }}" @selected($monitoring['filters']['priority'] === $key)>{{ __($label) }}</option>
            @endforeach
        </select></label>
        @foreach(['from' => 'Tarikh Dokumen Dari', 'to' => 'Tarikh Dokumen Hingga'] as $key => $label)
        <label class="text-sm text-gray-700 dark:text-gray-300">{{ __($label) }}<input type="date" name="{{ $key }}" value="{{ $monitoring['filters'][$key] }}" class="mt-1 block h-11 rounded-lg border border-gray-200 bg-transparent px-3 dark:border-gray-700"></label>
        @endforeach
        <button class="h-11 rounded-lg bg-brand-500 px-5 text-white dark:bg-brand-500">{{ __('Tapis') }}</button>
        <a href="{{ route('monitoring', ['tab' => $tab]) }}" class="document-action">{{ __('Reset') }}</a>
        @foreach($errors->all() as $error)<p class="w-full text-sm text-error-600 dark:text-error-400">{{ $error }}</p>@endforeach
    </form>
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Kad dan jadual mengikut penapis. Bayaran dikira sehingga hari ini; dokumen bertarikh akan datang tidak dimasukkan.') }}</p>
    @if($tab === 'lo')
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-dashboard.monitoring-card label="Jumlah LO" :value="$loRecords->count()"
                hint="Semua LO dalam pilihan semasa" :amounts="['Nilai LO' => $loRecords->sum('amount')]" />
            @php $completeOrders = $loRecords->where('complete', true); @endphp
            <x-dashboard.monitoring-card label="Dokumen Lengkap" :value="$completeOrders->count()" tone="success"
                hint="LO yang sudah mempunyai PO, DO dan invois" :amounts="['Nilai LO' => $completeOrders->sum('amount')]" />
            @foreach(['pos' => ['Belum Ada PO', 'LO yang belum dipautkan kepada PO pembekal'], 'dos' => ['Belum Ada DO', 'LO yang belum mempunyai DO pelanggan'], 'invoices' => ['Belum Ada Invois', 'LO yang belum mempunyai invois pelanggan']] as $key => [$label, $hint])
                @php $pendingOrders = $loRecords->filter(fn ($lo) => $lo->$key->isEmpty()); @endphp
                <x-dashboard.monitoring-card :label="$label" :value="$pendingOrders->count()" tone="warning"
                    :hint="$hint" :amounts="['Nilai LO' => $pendingOrders->sum('amount')]" />
            @endforeach
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Dokumen lengkap bermaksud PO, DO dan invois telah disediakan; bukan pengesahan penghantaran atau bayaran selesai.') }} {{ __('Nilai ialah amaun LO, bukan baki bayaran. Satu LO boleh termasuk dalam beberapa kategori dokumen belum ada.') }}</p>
        @include('pages.monitoring.lo-table')
    @else
        @php
            $rows = $tab === 'customer' ? $customerRecords : $supplierRecords;
            $money = fn ($value) => 'RM '.number_format($value, 2);
        @endphp
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-dashboard.monitoring-card :label="$tab === 'customer' ? 'Jumlah Jualan (Invois)' : 'Jumlah Pembelian (Invois)'" :value="$money($rows->sum('total'))" hint="Nilai penuh invois" />
            <x-dashboard.monitoring-card :label="$tab === 'customer' ? 'Jumlah Diterima' : 'Jumlah Dibayar'" :value="$money($rows->sum('paid'))" tone="success" hint="Bayaran invois yang telah direkod" />
            <x-dashboard.monitoring-card :label="$tab === 'customer' ? 'Baki Belum Diterima' : 'Baki Belum Dibayar'" :value="$money($rows->sum('balance'))" tone="warning" hint="Jumlah invois ditolak bayaran" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach(['Belum Dibayar' => 'brand', 'Bayaran Sebahagian' => 'warning', 'Selesai Dibayar' => 'success', 'Perlu Semakan' => 'error'] as $status => $tone)
            @php $statusRows = $rows->where('status', $status); @endphp
            <x-dashboard.monitoring-card :label="$status" :value="$statusRows->count()" :tone="$tone" hint="Bilangan invois"
                :amounts="['Jumlah Invois' => $statusRows->sum('total'), ($tab === 'customer' ? 'Jumlah Diterima' : 'Jumlah Dibayar') => $statusRows->sum('paid'), 'Baki' => $statusRows->sum('balance')]" />
            @endforeach
        </div>
        @if($tab === 'customer')
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ __('Pemantauan Bayaran dan Reminder') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-dashboard.monitoring-card label="Sudah Lewat" :value="$money($rows->where('priority', 'Sudah Lewat')->sum('balance'))" tone="error" hint="Baki melepasi tarikh akhir bayaran" />
                <x-dashboard.monitoring-card label="Hampir Jatuh Tempoh" :value="$money($rows->where('priority', 'Hampir Jatuh Tempoh')->sum('balance'))" tone="warning" hint="Tarikh akhir bayaran dalam 7 hari" />
            </div>
        @endif
        @if($tab === 'supplier')
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Rekod baucar menunjukkan semua baucar berkaitan invois, termasuk yang belum dibayar atau bertarikh akan datang. Sudah ada baucar tidak bermaksud invois selesai dibayar.') }}</p>
        @endif
        @include('pages.monitoring.payments-table')
        @if($tab === 'supplier')
            @include('pages.monitoring.vouchers-table')
        @endif
    @endif
</div>
@endsection
