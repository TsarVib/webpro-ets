<script lang="ts">
    import { Link, router, usePage } from "@inertiajs/svelte";
    import ThemeToggle from "@/components/ThemeToggle.svelte";
    import NavIcon from "@/components/ui/NavIcon.svelte";
    import { cn } from "@/lib/utils";
    import {
        categories,
        expense,
        home,
        income,
        logout,
        recap,
        settings,
    } from "@/routes";

    const items = [
        { label: "Dashboard", href: home.url(), icon: "dashboard" as const },
        { label: "Income", href: income.url(), icon: "income" as const },
        { label: "Expense", href: expense.url(), icon: "expense" as const },
        {
            label: "Categories",
            href: categories.url(),
            icon: "categories" as const,
        },
        { label: "Recap", href: recap.url(), icon: "recap" as const },
    ];

    const page = usePage();
    const currentPath = $derived(page.url.split("?")[0]);

    const itemClass = (href: string) =>
        cn(
            "flex h-10 shrink-0 items-center gap-2.5 rounded-lg px-3 text-sm font-medium transition-colors",
            "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
            currentPath === href
                ? "bg-primary/10 text-primary"
                : "text-muted hover:bg-background hover:text-ink",
        );

    let confirmingLogout = $state(false);
    let resetTimer: ReturnType<typeof setTimeout> | undefined;

    function handleLogout() {
        if (!confirmingLogout) {
            confirmingLogout = true;
            resetTimer = setTimeout(() => (confirmingLogout = false), 4000);

            return;
        }

        clearTimeout(resetTimer);
        router.post(logout.url());
    }
</script>

<aside
    class="flex w-full shrink-0 flex-col gap-4 border-b border-border bg-surface p-3 md:h-dvh md:w-60 md:justify-between md:gap-6 md:border-r md:border-b-0 md:p-4"
>
    <nav class="flex gap-1 overflow-x-auto md:grid md:gap-1">
        {#each items as item (item.href)}
            <Link href={item.href} class={itemClass(item.href)}>
                <NavIcon name={item.icon} class="size-4 shrink-0" />
                {item.label}
            </Link>
        {/each}
    </nav>

    <div class="flex items-center gap-2 md:grid md:gap-1">
        <div class="flex items-center justify-between gap-2">
            <Link
                href={settings.url()}
                class={cn(itemClass(settings.url()), "flex-1")}
            >
                <NavIcon name="settings" class="size-4 shrink-0" />
                Settings
            </Link>
            <ThemeToggle />
        </div>
        <button
            type="button"
            onclick={handleLogout}
            class={cn(
                "flex h-10 shrink-0 items-center gap-2.5 rounded-lg px-3 text-sm font-medium transition-colors",
                "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
                confirmingLogout
                    ? "bg-error/10 text-error hover:bg-error/20"
                    : "text-muted hover:bg-background hover:text-error",
            )}
        >
            <NavIcon name="logout" class="size-4 shrink-0" />
            {confirmingLogout ? "Are you sure?" : "Log out"}
        </button>
    </div>
</aside>
