@props(['inputClass' => '', 'required' => true, 'named' => true])
<div class="space-y-2" x-data="{
    unitMode: '',
    modeFor(value) { const unit = String(value || '').trim().toLowerCase(); return unit === 'unit' ? 'Unit' : (unit === 'lot' ? 'Lot' : (unit ? 'other' : '')); },
    chooseUnit(value) { this.unitMode = value; item.unit = value === 'other' ? '' : value; }
}" x-init="unitMode = modeFor(item.unit); $watch('item.unit', value => { if (value || unitMode !== 'other') unitMode = modeFor(value); })">
    @if($named)<input type="hidden" :name="`items[${index}][unit]`" :value="item.unit">@endif
    <select :value="unitMode" @change="chooseUnit($event.target.value)" @required($required) aria-label="{{ __('Unit') }}" class="{{ $inputClass }} w-full min-w-0 rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        <option value="">{{ __('Pilih unit') }}</option>
        <option value="Unit">{{ __('Unit') }}</option>
        <option value="Lot">{{ __('Lot') }}</option>
        <option value="other">{{ __('Lain-lain') }}</option>
    </select>
    <input x-cloak x-show="unitMode === 'other'" :disabled="unitMode !== 'other'" :required="{{ $required ? 'true' : 'false' }} && unitMode === 'other'" x-model="item.unit" maxlength="50" aria-label="{{ __('Unit lain') }}" placeholder="{{ __('Nyatakan unit') }}" class="{{ $inputClass }} w-full min-w-0 rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
</div>
