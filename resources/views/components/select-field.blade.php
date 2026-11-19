@props(['label', 'name'])
<div {{ $attributes->only('class') }}>
    <label for="f_{{ $name }}" class="mb-1 block text-xs font-semibold text-slate-600">
        {{ $label }}@if ($attributes->has('required')) <span class="text-red-600">*</span>@endif
    </label>
    <select id="f_{{ $name }}" name="{{ $name }}" {{ $attributes->except('class') }}
            class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-red-600 focus:ring-red-600 @error($name) border-red-500 @enderror">
        {{ $slot }}
    </select>
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
