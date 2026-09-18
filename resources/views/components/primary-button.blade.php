<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-br from-brand-500 to-brand-600 border border-transparent rounded-xl font-bold text-sm text-white shadow-brand-sm hover:shadow-brand hover:from-brand-400 hover:to-brand-500 active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all duration-200']) }}>
    {{ $slot }}
</button>
