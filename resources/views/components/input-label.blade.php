@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-slate-700 tracking-wide']) }}>
    {{ $value ?? $slot }}
</label>
