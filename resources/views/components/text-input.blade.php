@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 focus:border-brand-400 focus:ring-brand-400 rounded-xl shadow-sm text-sm text-gray-900 placeholder-gray-400 bg-white']) }}>
