@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:border-blue-400 focus:ring-blue-400 rounded-lg shadow-sm']) }}>
