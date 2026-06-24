<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-secondary border border-border/60 rounded-md font-semibold text-xs text-secondary-foreground uppercase tracking-widest shadow-sm hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-border focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
