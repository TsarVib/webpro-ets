<script lang="ts">
    import { cn } from "@/lib/utils";

    let dark = $state(
        typeof document !== "undefined" &&
            document.documentElement.classList.contains("dark"),
    );

    function toggle() {
        dark = !dark;
        document.documentElement.classList.toggle("dark", dark);
        localStorage.setItem("theme", dark ? "dark" : "light");
    }
</script>

<button
    type="button"
    onclick={toggle}
    aria-label={dark ? "Switch to light theme" : "Switch to dark theme"}
    class={cn(
        "grid size-9 place-items-center rounded-lg border border-border bg-surface text-muted transition-colors",
        "hover:text-ink hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
    )}
>
    {#if dark}
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" />
        </svg>
    {:else}
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
        </svg>
    {/if}
</button>
