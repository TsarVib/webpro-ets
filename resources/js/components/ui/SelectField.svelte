<script lang="ts">
    import { cn } from "@/lib/utils";

    let {
        label,
        name,
        value = $bindable(""),
        options,
        error,
        required = false,
        class: className,
    }: {
        label: string;
        name: string;
        value?: string;
        options: { value: string; label: string }[];
        error?: string;
        required?: boolean;
        class?: string;
    } = $props();

    const inputId = $derived(`field-${name}`);
    const errorId = $derived(`${inputId}-error`);
</script>

<div class={cn("grid gap-1.5", className)}>
    <label for={inputId} class="text-sm font-medium text-ink">
        {label}
        {#if required}
            <span class="text-neutral-500 dark:text-neutral-400" aria-hidden="true">*</span>
        {/if}
    </label>
    <select
        id={inputId}
        {name}
        bind:value
        required={required}
        aria-invalid={error ? "true" : undefined}
        aria-describedby={error ? errorId : undefined}
        class={cn(
            "h-10 w-full rounded-lg border bg-surface px-3 text-sm text-ink",
            error
                ? "border-error focus:outline-2 focus:outline-offset-2 focus:outline-error"
                : "border-border focus:outline-2 focus:outline-offset-2 focus:outline-ring",
        )}
    >
        {#each options as option (option.value)}
            <option value={option.value}>{option.label}</option>
        {/each}
    </select>
    {#if error}
        <p id={errorId} class="text-xs text-error" role="alert">
            {error}
        </p>
    {/if}
</div>
