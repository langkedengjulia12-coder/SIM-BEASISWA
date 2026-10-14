<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-950 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
