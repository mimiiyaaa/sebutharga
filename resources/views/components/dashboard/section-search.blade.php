@props(['name', 'placeholder', 'anchor', 'statuses' => false])
@php
    $allowed = ['start', 'end', 'lo_q', 'po_q', 'customer_q', 'supplier_q', 'invoice_q', 'status', 'age'];
    $exclude = $statuses ? [$name, 'status', 'age'] : [$name];
    $retained = collect(request()->only($allowed))->filter(fn ($value) => is_scalar($value))->except($exclude)->all();
    $resetUrl = route('dashboard', $retained).'#'.$anchor;
@endphp
<form method="GET" action="{{ route('dashboard') }}#{{ $anchor }}" class="flex flex-wrap items-end gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-800">
    @foreach($retained as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
    <div class="min-w-40 flex-1">
        <label for="dashboard-{{ $name }}" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __($placeholder) }}</label>
        <input id="dashboard-{{ $name }}" name="{{ $name }}" type="search" value="{{ request($name) }}" maxlength="200" placeholder="{{ __($placeholder) }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
    </div>
    @if($statuses)
    <div>
        <label for="dashboard-invoice-status" class="mb-2 block text-theme-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Status') }}</label>
        <select id="dashboard-invoice-status" name="status" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            <option value="">{{ __('Invois berbaki / perlu semakan') }}</option>
            @foreach(['Belum Dibayar','Bayaran Sebahagian','Selesai Dibayar','Perlu Semakan'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ __($status) }}</option>
            @endforeach
        </select>
    </div>
    @if(request()->filled('age'))<input type="hidden" name="age" value="{{ request('age') }}">@endif
    @endif
    <button class="document-action document-action-primary">{{ __('Cari') }}</button>
    <a class="document-action" href="{{ $resetUrl }}">{{ __('Reset') }}</a>
    <p class="w-full text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Carian ini hanya menapis bahagian ini. Tarikh mengikut penapis Dashboard di atas.') }}</p>
</form>
