@props(['customers' => collect()])

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="space-y-8">
        <x-common.component-card title="Maklumat Sebut Harga">
    <div class="space-y-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                No. Sebut Harga
            </label>
            <input
                type="text"
                value="Akan dijana secara automatik"
                readonly
                class="h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
            />
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Tarikh Sebut Harga
            </label>
            <x-form.date-picker
                id="quotation_date"
                name="quotation_date"
                placeholder="Pilih tarikh"
                defaultDate="{{ now()->format('Y-m-d') }}"
            />
        </div>

        <!-- <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Nama Pelanggan
            </label>
            <input
                type="text"
                name="customer_name"
                placeholder="Masukkan nama pelanggan"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
        </div> -->

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email Pelanggan
            </label>
            <input
                type="email"
                name="customer_email"
                placeholder="contoh@email.com"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
        </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Nama Pelanggan / Syarikat
        </label>
        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
            <select
    name="customer_id"
    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">

    <option value="">Pilih pelanggan / syarikat</option>

    @foreach ($customers as $customer)
        <option value="{{ $customer->customer_id }}">
            {{ $customer->company_name }}
            @if ($customer->customer_name)
                - {{ $customer->customer_name }}
            @endif
        </option>
    @endforeach
</select>
        </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Tajuk Sebut Harga
            </label>
            <input
                type="text"
                name="quotation_title"
                placeholder="Masukkan tajuk sebut harga"
                class="h-11 w-full rounded-lg border   border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
            />
        </div>
    </div>
        </x-common.component-card>

    {{-- text-area-inputs.blade.php --}}
<x-common.component-card title="Textarea input fields">
    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Description
        </label>
        <textarea placeholder="Enter a description..." type="text" rows="6"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-300 dark:text-white/15">
            Description
        </label>
        <textarea placeholder="Enter a description..." type="text" rows="6" disabled
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:shadow-focus-ring dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-0 focus:outline-hidden disabled:border-gray-100 disabled:bg-gray-50 disabled:placeholder:text-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:disabled:border-gray-800 dark:disabled:bg-white/[0.03] dark:disabled:placeholder:text-white/15"></textarea>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Description
        </label>
        <textarea placeholder="Enter a description..." type="text" rows="6"
            class="dark:bg-dark-900 border-error-300 shadow-theme-xs focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
        <p class="text-theme-xs text-error-500">
            Please enter a message in the textarea.
        </p>
    </div>
</x-common.component-card>

    {{-- input-states.blade.php --}}
<x-common.component-card 
  title="Input States"
  desc="Validation styles for error, success and disabled states on form controls."
>
    <div class="space-y-5 sm:space-y-6">
        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email
            </label>
            <div class="relative">
                <input type="text" value="demoemail"
                    class="dark:bg-dark-900 border-error-300 shadow-theme-xs focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800 w-full rounded-lg border bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <span class="absolute top-1/2 right-3.5 -translate-y-1/2">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.58325 7.99967C2.58325 5.00813 5.00838 2.58301 7.99992 2.58301C10.9915 2.58301 13.4166 5.00813 13.4166 7.99967C13.4166 10.9912 10.9915 13.4163 7.99992 13.4163C5.00838 13.4163 2.58325 10.9912 2.58325 7.99967ZM7.99992 1.08301C4.17995 1.08301 1.08325 4.17971 1.08325 7.99967C1.08325 11.8196 4.17995 14.9163 7.99992 14.9163C11.8199 14.9163 14.9166 11.8196 14.9166 7.99967C14.9166 4.17971 11.8199 1.08301 7.99992 1.08301ZM7.09932 5.01639C7.09932 5.51345 7.50227 5.91639 7.99932 5.91639H7.99999C8.49705 5.91639 8.89999 5.51345 8.89999 5.01639C8.89999 4.51933 8.49705 4.11639 7.99999 4.11639H7.99932C7.50227 4.11639 7.09932 4.51933 7.09932 5.01639ZM7.99998 11.8306C7.58576 11.8306 7.24998 11.4948 7.24998 11.0806V7.29627C7.24998 6.88206 7.58576 6.54627 7.99998 6.54627C8.41419 6.54627 8.74998 6.88206 8.74998 7.29627V11.0806C8.74998 11.4948 8.41419 11.8306 7.99998 11.8306Z"
                            fill="#F04438" />
                    </svg>
                </span>
            </div>

            <p class="text-theme-xs text-error-500 mt-1.5">
                This is an error message.
            </p>
        </div>

        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email
            </label>
            <div class="relative">
                <input type="text" value="demoemail@gmail.com"
                    class="dark:bg-dark-900 border-success-300 shadow-theme-xs focus:border-success-300 focus:ring-success-500/10 dark:border-success-700 dark:focus:border-success-800 w-full rounded-lg border bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <span class="absolute top-1/2 right-3.5 -translate-y-1/2">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.61792 8.00034C2.61792 5.02784 5.0276 2.61816 8.00009 2.61816C10.9726 2.61816 13.3823 5.02784 13.3823 8.00034C13.3823 10.9728 10.9726 13.3825 8.00009 13.3825C5.0276 13.3825 2.61792 10.9728 2.61792 8.00034ZM8.00009 1.11816C4.19917 1.11816 1.11792 4.19942 1.11792 8.00034C1.11792 11.8013 4.19917 14.8825 8.00009 14.8825C11.801 14.8825 14.8823 11.8013 14.8823 8.00034C14.8823 4.19942 11.801 1.11816 8.00009 1.11816ZM10.5192 7.266C10.8121 6.97311 10.8121 6.49823 10.5192 6.20534C10.2264 5.91245 9.75148 5.91245 9.45858 6.20534L7.45958 8.20434L6.54162 7.28638C6.24873 6.99349 5.77385 6.99349 5.48096 7.28638C5.18807 7.57927 5.18807 8.05415 5.48096 8.34704L6.92925 9.79533C7.0699 9.93599 7.26067 10.015 7.45958 10.015C7.6585 10.015 7.84926 9.93599 7.98991 9.79533L10.5192 7.266Z"
                            fill="#12B76A" />
                    </svg>
                </span>
            </div>

            <p class="text-theme-xs text-success-500 mt-1.5">
                This is an success message.
            </p>
        </div>

        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300 dark:text-white/15">
                Email
            </label>
            <input type="text" placeholder="info@gmail.com" disabled
                class="shadow-theme-xs focus:border-brand-300 focus:shadow-focus-ring dark:focus:border-brand-300 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden disabled:border-gray-100 disabled:placeholder:text-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-400 dark:disabled:border-gray-800 dark:disabled:placeholder:text-white/15" />
        </div>
    </div>
</x-common.component-card>

    </div>

    <div class="space-y-6">
    {{-- input-group.blade.php --}}
<x-common.component-card title="Input Group">
    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Email
        </label>
        <div class="relative">
            <span class="absolute top-1/2 ltr:left-0 rtl:right-0 -translate-y-1/2 ltr:border-r rtl:border-l border-gray-200 px-3.5 py-3 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.04175 7.06206V14.375C3.04175 14.6511 3.26561 14.875 3.54175 14.875H16.4584C16.7346 14.875 16.9584 14.6511 16.9584 14.375V7.06245L11.1443 11.1168C10.457 11.5961 9.54373 11.5961 8.85638 11.1168L3.04175 7.06206ZM16.9584 5.19262C16.9584 5.19341 16.9584 5.1942 16.9584 5.19498V5.20026C16.9572 5.22216 16.946 5.24239 16.9279 5.25501L10.2864 9.88638C10.1145 10.0062 9.8862 10.0062 9.71437 9.88638L3.07255 5.25485C3.05342 5.24151 3.04202 5.21967 3.04202 5.19636C3.042 5.15695 3.07394 5.125 3.11335 5.125H16.8871C16.9253 5.125 16.9564 5.15494 16.9584 5.19262ZM18.4584 5.21428V14.375C18.4584 15.4796 17.563 16.375 16.4584 16.375H3.54175C2.43718 16.375 1.54175 15.4796 1.54175 14.375V5.19498C1.54175 5.1852 1.54194 5.17546 1.54231 5.16577C1.55858 4.31209 2.25571 3.625 3.11335 3.625H16.8871C17.7549 3.625 18.4584 4.32843 18.4585 5.19622C18.4585 5.20225 18.4585 5.20826 18.4584 5.21428Z"
                        fill="#667085" />
                </svg>
            </span>
            <input type="text" placeholder="info@gmail.com" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 ltr:pl-[62px] rtl:pr-[62px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div x-data="{
        selectedCountry: 'US',
        countryCodes: {
            'US': '+1',
            'GB': '+44',
            'CA': '+1',
            'AU': '+61'
        },
        phoneNumber: ''
    }">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Phone
        </label>
        <div class="relative">
            <div class="absolute ltr:left-0 rtl:right-0">
                <select x-model="selectedCountry" @change="phoneNumber = countryCodes[selectedCountry]"
                    class="focus:border-brand-300 focus:ring-brand-500/10 appearance-none ltr:rounded-l-lg rtl:rounded-r-lg rtl:rounded-l-none border-0 ltr:border-r rtl:border-l border-gray-200 bg-transparent bg-none py-3 ltr:pr-8 ltr:pl-3.5 rtl:pl-8 rtl:pr-3.5 leading-tight text-gray-700 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:text-gray-400">
                    <option value="US" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        US
                    </option>
                    <option value="GB" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        GB
                    </option>
                    <option value="CA" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        CA
                    </option>
                    <option value="AU" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        AU
                    </option>
                    <!-- Add more country codes as needed -->
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 ltr:right-3 rtl:left-3 flex items-center text-gray-700 dark:text-gray-400">
                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <input placeholder="+1 (555) 000-0000" x-model="phoneNumber" type="tel"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pr-4 ltr:pl-[84px] rtl:pl-4 rtl:pr-[84px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div x-data="{
        selectedCountry: 'US',
        countryCodes: {
            'US': '+1',
            'GB': '+44',
            'CA': '+1',
            'AU': '+61'
        },
        phoneNumber: ''
    }">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Phone
        </label>
        <div class="relative">
            <div class="absolute ltr:right-0 rtl:left-0">
                <select x-model="selectedCountry" @change="phoneNumber = countryCodes[selectedCountry]"
                    class="focus:border-brand-300 focus:ring-brand-500/10 appearance-none ltr:rounded-r-lg rtl:rounded-l-lg rtl:rounded-r-none border-0 ltr:border-l rtl:border-r border-gray-200 bg-transparent bg-none py-3 ltr:pr-8 ltr:pl-3.5 rtl:pl-8 rtl:pr-3.5 leading-tight text-gray-700 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:text-gray-400">
                    <option value="US" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        US
                    </option>
                    <option value="GB" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        GB
                    </option>
                    <option value="CA" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        CA
                    </option>
                    <option value="AU" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        AU
                    </option>
                    <!-- Add more country codes as needed -->
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 ltr:right-3 rtl:left-3 flex items-center text-gray-700 dark:text-gray-400">
                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <input placeholder="+1 (555) 000-0000" x-model="phoneNumber" type="tel"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pl-4 ltr:pr-[84px] rtl:pr-4 rtl:pl-[84px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            URL
        </label>
        <div class="relative">
            <span
                class="absolute top-1/2 ltr:left-0 rtl:right-0 inline-flex h-11 -translate-y-1/2 items-center justify-center ltr:border-r rtl:border-l border-gray-200 py-3 ltr:pr-3 ltr:pl-3.5 rtl:pl-3 rtl:pr-3.5 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                http://
            </span>
            <input type="url" placeholder="www.tailadmin.com"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 ltr:pl-[90px] rtl:pr-[90px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>

    <!-- Elements -->
    <div id="copy-input">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Website
        </label>
        <div class="relative">
            <button id="copy-button"
                class="absolute top-1/2 ltr:right-0 rtl:left-0 inline-flex -translate-y-1/2 cursor-pointer items-center gap-1 ltr:border-l rtl:border-r border-gray-200 py-3 ltr:pr-3 ltr:pl-3.5 rtl:pl-3 rtl:pr-3.5 text-sm font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.58822 4.58398C6.58822 4.30784 6.81207 4.08398 7.08822 4.08398H15.4154C15.6915 4.08398 15.9154 4.30784 15.9154 4.58398L15.9154 12.9128C15.9154 13.189 15.6916 13.4128 15.4154 13.4128H7.08821C6.81207 13.4128 6.58822 13.189 6.58822 12.9128V4.58398ZM7.08822 2.58398C5.98365 2.58398 5.08822 3.47942 5.08822 4.58398V5.09416H4.58496C3.48039 5.09416 2.58496 5.98959 2.58496 7.09416V15.4161C2.58496 16.5207 3.48039 17.4161 4.58496 17.4161H12.9069C14.0115 17.4161 14.9069 16.5207 14.9069 15.4161L14.9069 14.9128H15.4154C16.52 14.9128 17.4154 14.0174 17.4154 12.9128L17.4154 4.58398C17.4154 3.47941 16.52 2.58398 15.4154 2.58398H7.08822ZM13.4069 14.9128H7.08821C5.98364 14.9128 5.08822 14.0174 5.08822 12.9128V6.59416H4.58496C4.30882 6.59416 4.08496 6.81801 4.08496 7.09416V15.4161C4.08496 15.6922 4.30882 15.9161 4.58496 15.9161H12.9069C13.183 15.9161 13.4069 15.6922 13.4069 15.4161L13.4069 14.9128Z"
                        fill="" />
                </svg>
                <div id="copy-text">Copy</div>
            </button>
            <input value="www.tailadmin.com" type="url" id="website-input"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-3 ltr:pr-[90px] ltr:pl-4 rtl:pl-[90px] rtl:pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
    </div>
</x-common.component-card>

    {{-- file-input-example.blade.php --}}
<x-common.component-card title="File Input">
    <!-- Elements -->
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Upload file
        </label>
        <input type="file"
            class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors ltr:file:mr-5 rtl:file:ml-5 rtl:file:mr-0 file:border-collapse file:cursor-pointer ltr:file:rounded-l-lg rtl:file:rounded-r-lg rtl:file:rounded-l-none file:border-0 ltr:file:border-r rtl:file:border-l rtl:file:border-r-0 file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 ltr:file:pr-3 ltr:file:pl-3.5 rtl:file:pl-3 rtl:file:pr-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400" />
    </div>
</x-common.component-card>

    {{-- checkbox-component.blade.php --}}
<x-common.component-card title="Checkboxes">
    <div class="flex flex-wrap items-center gap-8">
        <div x-data="{ checkboxToggle: false }">
            <label for="checkboxLabelOne"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelOne" class="sr-only"
                        @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>
                Default
            </label>
        </div>

        <div x-data="{ checkboxToggle: true }">
            <label for="checkboxLabelTwo"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelTwo" class="sr-only"
                        @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>
                Checked
            </label>
        </div>

        <div x-data="{ checkboxToggle: true }">
            <label for="checkboxLabelThree"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-300 select-none dark:text-gray-700">
                <div class="relative">
                    <input type="checkbox" id="checkboxLabelThree" class="peer sr-only"
                        @change="checkboxToggle = !checkboxToggle" disabled />
                    <div :class="checkboxToggle ? 'bg-transparent border-gray-200 dark:border-gray-800' :
                        'border-brand-500 bg-brand-500'"
                        class="ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                            <svg class="stroke-gray-200 dark:stroke-gray-800" width="14" height="14"
                                viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="" stroke-width="2.33333"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>
                Disabled
            </label>
        </div>
    </div>
</x-common.component-card>

    {{-- radio-buttons.blade.php --}}
<x-common.component-card title="Radio Buttons">
    <div class="flex flex-wrap items-center gap-8">
        <div x-data="{ checkboxToggle: false }">
            <label for="radioLabelOne"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="radioLabelOne" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-full border-[1.25px]">
                        <span class="h-2 w-2 rounded-full"
                            :class="checkboxToggle ? 'bg-white' : 'bg-white dark:bg-[#171f2e]'"></span>
                    </div>
                </div>
                Default
            </label>
        </div>

        <div x-data="{ checkboxToggle: true }">
            <label for="radioLabelTwo"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="radioLabelTwo" class="sr-only"
                        @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                        'bg-transparent border-gray-300 dark:border-gray-700'"
                        class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-full border-[1.25px]">
                        <span class="h-2 w-2 rounded-full"
                            :class="checkboxToggle ? 'bg-white' : 'bg-white dark:bg-[#171f2e]'"></span>
                    </div>
                </div>
                Secondary
            </label>
        </div>

        <div x-data="{ checkboxToggle: false }">
            <label for="radioLabelThree"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-300 select-none dark:text-gray-700">
                <div class="relative">
                    <input type="checkbox" id="radioLabelThree" class="peer sr-only"
                        @change="checkboxToggle = !checkboxToggle" disabled />
                    <div :class="checkboxToggle ? 'bg-transparent border-gray-300 dark:border-gray-700' :
                        'border-brand-500 bg-brand-500'"
                        class="ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-full border-[1.25px]">
                        <span class="h-2 w-2 rounded-full"
                            :class="checkboxToggle ? 'bg-white' : 'bg-white dark:bg-[#171f2e]'"></span>
                    </div>
                </div>
                Disabled Secondary
            </label>
        </div>
    </div>
</x-common.component-card>

    {{-- toggle-switch.blade.php --}}
<x-common.component-card title="Toggle switch input">
    <!-- Elements -->
    <div class="mb-6 flex flex-wrap items-center gap-6 sm:gap-8">
        <div x-data="{ switcherToggle: false }">
            <label for="toggle1"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="toggle1" class="sr-only" @change="switcherToggle = !switcherToggle" />
                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'">
                    </div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear">
                    </div>
                </div>

                Default
            </label>
        </div>

        <div x-data="{ switcherToggle: true }">
            <label for="toggle2"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="toggle2" class="sr-only" @change="switcherToggle = !switcherToggle" />
                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'">
                    </div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear">
                    </div>
                </div>

                Checked
            </label>
        </div>

        <div x-data="{ switcherToggle: false }">
            <label for="toggle3"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-400 select-none">
                <div class="relative">
                    <input type="checkbox" id="toggle3" class="sr-only" @change="switcherToggle = !switcherToggle"
                        disabled />
                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-100 dark:bg-gray-800'">
                    </div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-gray-50 duration-300 ease-linear">
                    </div>
                </div>

                Disabled
            </label>
        </div>
    </div>

    <!-- Elements -->
    <div class="flex flex-wrap items-center gap-6 sm:gap-8">
        <div x-data="{ switcherToggle: false }">
            <label for="toggle11"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="toggle11" class="sr-only" @change="switcherToggle = !switcherToggle" />
                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-gray-700 dark:bg-white/10' : 'bg-gray-200 dark:bg-gray-800'"></div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear">
                    </div>
                </div>

                Default
            </label>
        </div>

        <div x-data="{ switcherToggle: true }">
            <label for="toggle22"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                    <input type="checkbox" id="toggle22" class="sr-only" @change="switcherToggle = !switcherToggle" />

                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-gray-700 dark:bg-white/10' : 'bg-gray-200 dark:bg-gray-800'"></div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear">
                    </div>
                </div>

                Checked
            </label>
        </div>

        <div x-data="{ switcherToggle: false }">
            <label for="toggle33"
                class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-400 select-none">
                <div class="relative">
                    <input type="checkbox" id="toggle33" class="sr-only" @change="switcherToggle = !switcherToggle"
                        disabled />
                    <div class="block h-6 w-11 rounded-full"
                        :class="switcherToggle ? 'bg-gray-700 dark:bg-white/10' : 'bg-gray-100 dark:bg-gray-800'">
                    </div>
                    <div :class="switcherToggle ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
                        class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-gray-50 duration-300 ease-linear">
                    </div>
                </div>

                Disabled
            </label>
        </div>
    </div>
</x-common.component-card>

    {{-- dropzone.blade.php --}}
<x-common.component-card title="Dropzone">
    <!-- Dropzone -->
    <div 
        x-data="{
            isDragging: false,
            files: [],
            handleDrop(e) {
                this.isDragging = false;
                const droppedFiles = Array.from(e.dataTransfer.files);
                this.handleFiles(droppedFiles);
            },
            handleFiles(selectedFiles) {
                const validTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
                const validFiles = selectedFiles.filter(file => validTypes.includes(file.type));
                
                if (validFiles.length > 0) {
                    this.files = [...this.files, ...validFiles];
                    console.log('Files uploaded:', validFiles);
                    
                    // Here you can add logic to upload files to server
                    this.uploadFiles(validFiles);
                }
            },
            uploadFiles(files) {
                // Implement your file upload logic here
                // Example: Use FormData and fetch/axios to upload
                console.log('Uploading files:', files);
            },
            removeFile(index) {
                this.files.splice(index, 1);
            }
        }"
        class="transition border border-gray-300 border-dashed cursor-pointer dark:hover:border-brand-500 dark:border-gray-700 rounded-xl hover:border-brand-500"
    >
        <div 
            @drop.prevent="handleDrop($event)"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @click="$refs.fileInput.click()"
            :class="isDragging 
                ? 'border-brand-500 bg-gray-100 dark:bg-gray-800' 
                : 'border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-900'"
            class="dropzone rounded-xl border-dashed border-gray-300 p-7 lg:p-10 transition-colors cursor-pointer"
            id="demo-upload"
        >
            <!-- Hidden File Input -->
            <input 
                x-ref="fileInput"
                type="file" 
                @change="handleFiles(Array.from($event.target.files)); $event.target.value = ''"
                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                multiple
                class="hidden"
                @click.stop
            />

            <div class="flex flex-col items-center m-0">
                <!-- Icon Container -->
                <div class="mb-[22px] flex justify-center">
                    <div class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg
                            class="fill-current"
                            width="29"
                            height="28"
                            viewBox="0 0 29 28"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M14.5019 3.91699C14.2852 3.91699 14.0899 4.00891 13.953 4.15589L8.57363 9.53186C8.28065 9.82466 8.2805 10.2995 8.5733 10.5925C8.8661 10.8855 9.34097 10.8857 9.63396 10.5929L13.7519 6.47752V18.667C13.7519 19.0812 14.0877 19.417 14.5019 19.417C14.9161 19.417 15.2519 19.0812 15.2519 18.667V6.48234L19.3653 10.5929C19.6583 10.8857 20.1332 10.8855 20.426 10.5925C20.7188 10.2995 20.7186 9.82463 20.4256 9.53184L15.0838 4.19378C14.9463 4.02488 14.7367 3.91699 14.5019 3.91699ZM5.91626 18.667C5.91626 18.2528 5.58047 17.917 5.16626 17.917C4.75205 17.917 4.41626 18.2528 4.41626 18.667V21.8337C4.41626 23.0763 5.42362 24.0837 6.66626 24.0837H22.3339C23.5766 24.0837 24.5839 23.0763 24.5839 21.8337V18.667C24.5839 18.2528 24.2482 17.917 23.8339 17.917C23.4197 17.917 23.0839 18.2528 23.0839 18.667V21.8337C23.0839 22.2479 22.7482 22.5837 22.3339 22.5837H6.66626C6.25205 22.5837 5.91626 22.2479 5.91626 21.8337V18.667Z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Text Content -->
                <h4 class="mb-3 font-semibold text-gray-800 text-theme-xl dark:text-white/90">
                    <span x-show="!isDragging">Drag & Drop Files Here</span>
                    <span x-show="isDragging" x-cloak>Drop Files Here</span>
                </h4>

                <span class="text-center mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                    Drag and drop your PNG, JPG, WebP, SVG images here or browse
                </span>

                <span class="font-medium underline text-theme-sm text-brand-500">
                    Browse File
                </span>
            </div>
        </div>

        <!-- File Preview List (Optional) -->
        <div x-show="files.length > 0" class="mt-4 p-4 border-t border-gray-200 dark:border-gray-700" x-cloak>
            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Uploaded Files:</h5>
            <ul class="space-y-2">
                <template x-for="(file, index) in files" :key="index">
                    <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="file.name"></span>
                        </div>
                        <button 
                            @click.stop="removeFile(index)"
                            type="button"
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</x-common.component-card>
</div>
