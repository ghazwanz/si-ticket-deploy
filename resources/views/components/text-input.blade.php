@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-border/60 bg-background text-foreground focus:border-primary focus:ring-primary rounded-md shadow-sm']) }}>
