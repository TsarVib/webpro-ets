<script lang="ts">
    import type { FullAutoFill } from "svelte/elements";
    import { cn } from "@/lib/utils";

    let {
        label,
        name,
        value = $bindable(""),
        type = "text",
        error,
        placeholder,
        autocomplete,
        required = false,
        min,
        step,
        class: className,
    }: {
        label: string;
        name: string;
        value?: string;
        type?: "text" | "email" | "password" | "number" | "date" | "time";
        error?: string;
        placeholder?: string;
        autocomplete?: FullAutoFill;
        required?: boolean;
        min?: string | number;
        step?: string | number;
        class?: string;
    } = $props();

    let typeOverride: "text" | null = $state(null);

    const inputId = $derived(`field-${name}`);
    const errorId = $derived(`${inputId}-error`);
    const isPassword = $derived(type === "password");
    const revealed = $derived(isPassword && typeOverride === "text");
</script>

<div class={cn("grid gap-1.5", className)}>
    <label for={inputId} class="text-sm font-medium text-ink">
        {label}
        {#if required}
            <span class="text-neutral-500 dark:text-neutral-400" aria-hidden="true">*</span>
        {/if}
    </label>
    <div class="relative">
        <input
            id={inputId}
            {name}
            type={typeOverride ?? type}
            bind:value
            {placeholder}
            {autocomplete}
            required={required}
            {min}
            {step}
            aria-invalid={error ? "true" : undefined}
            aria-describedby={error ? errorId : undefined}
            class={cn(
                "h-10 w-full rounded-lg border bg-surface px-3 text-sm text-ink placeholder:text-muted",
                error
                    ? "border-error focus:outline-2 focus:outline-offset-2 focus:outline-error"
                    : "border-border focus:outline-2 focus:outline-offset-2 focus:outline-ring",
                isPassword && "pr-14",
            )}
        />
        {#if isPassword}
            <button
                type="button"
                onclick={() => (typeOverride = revealed ? null : "text")}
                class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-medium text-muted underline-offset-2 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
            >
                {revealed ? "Hide" : "Show"}
            </button>
        {/if}
    </div>
    {#if error}
        <p id={errorId} class="text-xs text-error" role="alert">
            {error}
        </p>
    {/if}
</div>
