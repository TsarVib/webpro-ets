<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
</script>

<AppHead title="Expense" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="grid w-full flex-1 content-start gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="grid gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">
                Expense
            </h1>
            <p class="text-sm text-muted">
                All money going out shows up here.
            </p>
        </div>

        <div class="grid gap-2 rounded-xl border border-border bg-surface p-6">
            <h2 class="text-sm font-medium text-ink">No expense entries yet</h2>
            <p class="text-sm text-muted">
                Once you record an expense, it will appear in this list.
            </p>
        </div>
    </main>
</div>
