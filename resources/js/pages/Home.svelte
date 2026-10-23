<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
</script>

<AppHead title="Dashboard" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="grid w-full flex-1 content-start gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="grid gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">
                Dashboard
            </h1>
            <p class="text-sm text-muted">
                Here's your money overview.
            </p>
        </div>

        <div
            class="grid gap-2 rounded-xl border border-border bg-surface p-6"
        >
            <h2 class="text-sm font-medium text-ink">Nothing here yet</h2>
            <p class="text-sm text-muted">
                Add your first income or expense entry to see it show up here.
            </p>
        </div>
    </main>
</div>
