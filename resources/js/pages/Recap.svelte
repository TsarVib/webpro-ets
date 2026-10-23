<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
</script>

<AppHead title="Recap" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="grid w-full flex-1 content-start gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="grid gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">
                Recap
            </h1>
            <p class="text-sm text-muted">
                Monthly income, expense, and category totals.
            </p>
        </div>

        <div class="grid gap-2 rounded-xl border border-border bg-surface p-6">
            <h2 class="text-sm font-medium text-ink">No data yet</h2>
            <p class="text-sm text-muted">
                Totals appear once this month has transactions.
            </p>
        </div>
    </main>
</div>
