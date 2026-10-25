<script lang="ts">
    import type { Snippet } from "svelte";
    import { cn } from "@/lib/utils";

    let {
        type = "button",
        variant = "primary",
        loading = false,
        disabled = false,
        class: className,
        onclick,
        children,
    }: {
        type?: "button" | "submit";
        variant?: "primary" | "secondary";
        loading?: boolean;
        disabled?: boolean;
        class?: string;
        onclick?: (event: MouseEvent) => void;
        children: Snippet;
    } = $props();
</script>

                <button
    {type}
    {onclick}
    aria-busy={loading || undefined}
    disabled={disabled || loading}
    class={cn(
        "inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium transition-colors duration-150",
        "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
        "disabled:cursor-not-allowed disabled:opacity-50",
        variant === "primary" &&
            "bg-primary text-primary-ink hover:bg-primary-hover",
        variant === "secondary" &&
            "border border-border bg-surface text-ink hover:bg-background",
        className,
    )}
>
    {#if loading}
        <svg
            class="size-4 animate-spin"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
            />
        </svg>
    {/if}
    {@render children()}
</button>
