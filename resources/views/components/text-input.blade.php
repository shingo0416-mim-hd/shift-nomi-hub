@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-12 rounded-xl border border-slate-300 bg-white px-4 py-3 text-base leading-normal text-slate-950 placeholder-slate-400 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500']) }}>
