@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$contact ? __('Edit') : __('Tambah')" />
    <div class="w-full">
        <form method="POST" action="{{ route('contacts.save', ['type' => $type, 'id' => $contact?->customer_id]) }}" class="space-y-6">
            @csrf
            <x-common.document-card :title="$contact ? __('Kemaskini Maklumat') : __('Maklumat '.ucfirst($type))" :desc="__('Lengkapkan maklumat di bawah untuk disimpan dalam sistem.')">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kod') }}</label>
                        <input readonly value="{{ $contact?->customer_code ?: $generatedCode }}" class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    </div>
                    <div>
                        <label for="customer_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama') }}</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', $contact?->customer_name) }}" class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">
                    </div>
                    @foreach (['company_name' => 'Nama Syarikat', 'phone_no' => 'Nombor Telefon', 'email' => 'E-mel', 'reference_no' => 'No. Rujukan'] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($label) }} @if($field === 'company_name')<span class="text-error-500">*</span>@endif</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'phone_no' ? 'tel' : 'text') }}" value="{{ old($field, $contact?->$field) }}" @required($field === 'company_name') class="min-h-11 w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">
                            @error($field)<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div class="md:col-span-2">
                        <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Alamat') }}</label>
                        <textarea id="address" name="address" rows="4" required class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/60 dark:text-white/90">{{ old('address', $contact?->address) }}</textarea>
                        @error('address')<p class="mt-1.5 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-common.document-card>
            <div class="flex justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
                <a href="{{ url('/'.$type) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ __('Kembali') }}</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan') }}</button>
            </div>
        </form>
    </div>
@endsection
