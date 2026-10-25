<script lang="ts">
    import type { PaginationLink } from "@/types/transaction";
    import { Link } from "@inertiajs/svelte";
    import { cn } from "@/lib/utils";

    let {
        links,
        from,
        to,
        total,
    }: {
        links: PaginationLink[];
        from: number | null;
        to: number | null;
        total: number;
    } = $props();

    const cleanLabel = (label: string) =>
        label
            .replaceAll("&laquo;", "«")
            .replaceAll("&raquo;", "»")
            .replaceAll("&amp;", "&");
</script>

{#if total > 0}
    <nav
        class="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3"
        aria-label="Pagination"
    >
        <p class="text-xs text-muted">
            Showing {from ?? 0}–{to ?? 0} of {total}
        </p>

        <div class="flex flex-wrap items-center gap-1">
            {#each links as link, index (index)}
                {#if link.url}
                    <Link
                        href={link.url}
                        preserveScroll
                        class={cn(
                            "grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-medium transition-colors",
                            "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
                            link.active
                                ? "bg-primary/10 text-primary"
                                : "text-muted hover:bg-background hover:text-ink",
                        )}
                        aria-current={link.active ? "page" : undefined}
                    >
                        {cleanLabel(link.label)}
                    </Link>
                {:else}
                    <span
                        class="grid h-8 min-w-8 place-items-center px-2 text-xs text-muted"
                    >
                        {cleanLabel(link.label)}
                    </span>
                {/if}
            {/each}
        </div>
    </nav>
{/if}
