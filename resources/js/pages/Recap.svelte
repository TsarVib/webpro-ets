<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
    import Button from "@/components/ui/Button.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import { recap } from "@/routes";
    import { csv, pdf, report } from "@/routes/recap";
    import { router, usePage } from "@inertiajs/svelte";

    type CategoryTotal = {
        category: string;
        total: number;
    };

    type PageProps = {
        mode: "month" | "range" | "year";
        month: string;
        from: string;
        to: string;
        year: string;
        period_label: string;
        summary: {
            income: number;
            expense: number;
            net: number;
        };
        category_totals: {
            income: CategoryTotal[];
            expense: CategoryTotal[];
        };
    };

    const page = usePage<PageProps>();
    const periodLabel = $derived(page.props.period_label);
    const summary = $derived(page.props.summary);
    const categoryTotals = $derived(page.props.category_totals);
    let reportMode = $state<"month" | "range" | "year">(page.props.mode);
    let chartMode = $state<"bar" | "wheel">("bar");
    const chartGroups = $derived([
        { title: "Income by category", items: categoryTotals.income },
        { title: "Expense by category", items: categoryTotals.expense },
    ]);
    const chartColors = [
        "--swatch-green",
        "--swatch-blue",
        "--swatch-peach",
        "--swatch-mauve",
        "--swatch-teal",
        "--swatch-yellow",
        "--swatch-pink",
        "--swatch-sapphire",
        "--swatch-lavender",
    ];
    const reportQuery = $derived({
        query: {
            mode: reportMode,
            ...(reportMode === "month"
                ? { month: page.props.month }
                : reportMode === "range"
                  ? { from: page.props.from, to: page.props.to }
                  : { year: page.props.year }),
        },
    });

    const money = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    });

    function applyReport(event: SubmitEvent) {
        event.preventDefault();
        const data = new FormData(event.currentTarget as HTMLFormElement);
        const query: Record<string, string> = {
            mode: reportMode,
        };

        for (const key of ["month", "from", "to", "year"]) {
            const value = String(data.get(key) ?? "");

            if (value) {
                query[key] = value;
            }
        }

        router.get(recap.url(), query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    function barHeight(items: CategoryTotal[], total: number) {
        const maximum = Math.max(...items.map((item) => item.total), 0);

        return maximum > 0 ? Math.max((total / maximum) * 100, 4) : 0;
    }

    function wheelStyle(items: CategoryTotal[]) {
        const total = items.reduce((sum, item) => sum + item.total, 0);
        let position = 0;
        const segments = items.map((item, index) => {
            const start = position;
            position += total > 0 ? (item.total / total) * 100 : 0;

            return `var(${chartColors[index % chartColors.length]}) ${start}% ${position}%`;
        });

        return `background: conic-gradient(${segments.join(", ")})`;
    }
</script>

<AppHead title="Recap" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="flex min-h-0 w-full flex-1 flex-col gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-tight text-ink">
                    Recap
                </h1>
                <p class="text-sm text-muted">
                    Income and expense totals for {periodLabel}.
                </p>
            </div>

            <form class="grid gap-3 sm:flex sm:items-end" onsubmit={applyReport}>
                <div class="grid gap-1.5">
                    <label for="report-mode" class="text-sm font-medium text-ink">
                        Report
                    </label>
                    <select
                        id="report-mode"
                        bind:value={reportMode}
                        class="h-10 rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                    >
                        <option value="month">Single month</option>
                        <option value="range">Month range</option>
                        <option value="year">Full year</option>
                    </select>
                </div>
                {#if reportMode === "month"}
                    <TextField label="Month" name="month" type="month" value={page.props.month} />
                {:else if reportMode === "range"}
                    <TextField label="From" name="from" type="month" value={page.props.from} />
                    <TextField label="To" name="to" type="month" value={page.props.to} />
                {:else}
                    <TextField label="Year" name="year" type="number" value={page.props.year} min="2000" max="2100" />
                {/if}
                <Button type="submit">View report</Button>
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-[repeat(3,minmax(0,1fr))_auto]">
            <section class="grid gap-1 rounded-xl border border-border bg-surface p-4">
                <p class="text-xs text-muted">Income</p>
                <p class="text-lg font-semibold text-primary">
                    {money.format(summary.income)}
                </p>
            </section>
            <section class="grid gap-1 rounded-xl border border-border bg-surface p-4">
                <p class="text-xs text-muted">Expense</p>
                <p class="text-lg font-semibold text-error">
                    {money.format(summary.expense)}
                </p>
            </section>
            <section class="grid gap-1 rounded-xl border border-border bg-surface p-4">
                <p class="text-xs text-muted">Net change</p>
                <p
                    class="text-lg font-semibold"
                    class:text-primary={summary.net >= 0}
                    class:text-error={summary.net < 0}
                >
                    {money.format(summary.net)}
                </p>
            </section>
            <section class="flex w-fit flex-wrap items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2">
                <p class="mr-1 text-xs font-medium text-muted">Export</p>
                <div class="flex flex-wrap gap-1.5">
                    <a
                        href={csv.url(reportQuery)}
                        class="inline-flex h-8 items-center justify-center rounded-md border border-border bg-surface px-2.5 text-xs font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        CSV
                    </a>
                    <a
                        href={report.url(reportQuery)}
                        target="_blank"
                        rel="noreferrer"
                        class="inline-flex h-8 items-center justify-center rounded-md border border-border bg-surface px-2.5 text-xs font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        Print
                    </a>
                    <a
                        href={pdf.url(reportQuery)}
                        class="inline-flex h-8 items-center justify-center rounded-md bg-primary px-2.5 text-xs font-medium text-primary-ink transition-colors hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        PDF
                    </a>
                </div>
            </section>
        </div>

        {#if summary.income === 0 && summary.expense === 0}
            <div class="grid gap-2 rounded-xl border border-border bg-surface p-6">
                <h2 class="text-sm font-medium text-ink">No data for this month</h2>
                <p class="text-sm text-muted">
                    Record an income or expense to see the breakdown here.
                </p>
            </div>
        {:else}
            <section class="grid gap-4 rounded-xl border border-border bg-surface p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="grid gap-1">
                        <h2 class="text-sm font-medium text-ink">
                            Period comparison
                        </h2>
                        <p class="text-xs text-muted">
                            Compare categories for {periodLabel}.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="chart-mode" class="sr-only">Chart view</label>
                        <select
                            id="chart-mode"
                            bind:value={chartMode}
                            class="h-9 rounded-lg border border-border bg-surface px-2.5 text-xs font-medium text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                        >
                            <option value="bar">Bar chart</option>
                            <option value="wheel">Wheel chart</option>
                        </select>
                    </div>
                </div>

                <div class="min-h-64">
                {#if chartMode === "bar"}
                    <div class="grid h-64 gap-5 sm:grid-cols-2" aria-label="Category bar charts">
                        {#each chartGroups as group}
                            <div class="grid content-start gap-2">
                                <h3 class="text-sm font-medium text-ink">{group.title}</h3>
                                {#if group.items.length === 0}
                                    <p class="text-sm text-muted">No categories recorded.</p>
                                {:else}
                                    <div class="flex min-h-48 items-end gap-3 overflow-x-auto px-2 pt-4">
                                        {#each group.items as item, index (item.category)}
                                            <div class="grid min-w-20 flex-1 justify-items-center gap-2">
                                                <span class="text-center text-xs font-medium text-muted">
                                                    {money.format(item.total)}
                                                </span>
                                                <div class="flex h-32 w-full items-end rounded-t-lg bg-background">
                                                    <div
                                                        class="w-full rounded-t-lg"
                                                        style={`height: ${barHeight(group.items, item.total)}%; background: var(${chartColors[index % chartColors.length]})`}
                                                        aria-hidden="true"
                                                    ></div>
                                                </div>
                                                <span class="max-w-24 truncate text-center text-xs text-ink" title={item.category}>
                                                    {item.category}
                                                </span>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {:else}
                    <div class="grid h-64 gap-5 sm:grid-cols-2" aria-label="Category wheel charts">
                        {#each chartGroups as group}
                            {@const groupTotal = group.items.reduce((sum, item) => sum + item.total, 0)}
                            <div class="grid content-start gap-2">
                                <h3 class="text-sm font-medium text-ink">{group.title}</h3>
                                {#if group.items.length === 0}
                                    <p class="text-sm text-muted">No categories recorded.</p>
                                {:else}
                                    <div class="flex flex-wrap items-center justify-center gap-5">
                                        <div
                                            class="grid size-40 place-items-center rounded-full"
                                            style={wheelStyle(group.items)}
                                            role="img"
                                            aria-label={`${group.title}: ${money.format(groupTotal)}`}
                                        >
                                            <div class="grid size-24 place-items-center rounded-full bg-surface text-center">
                                                <span class="text-xs text-muted">Total</span>
                                                <span class="text-sm font-semibold text-ink">
                                                    {money.format(groupTotal)}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="grid max-w-full gap-2">
                                            {#each group.items as item, index (item.category)}
                                                <div class="flex min-w-0 items-center gap-2 text-xs text-ink">
                                                    <span
                                                        class="size-2.5 shrink-0 rounded-full"
                                                        style={`background: var(${chartColors[index % chartColors.length]})`}
                                                        aria-hidden="true"
                                                    ></span>
                                                    <span class="max-w-32 truncate" title={item.category}>
                                                        {item.category}
                                                    </span>
                                                    <strong>{money.format(item.total)}</strong>
                                                </div>
                                            {/each}
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {/if}
                </div>
            </section>

            <div class="grid min-h-0 flex-1 gap-6 sm:grid-cols-2">
                {#each [
                    { title: "Income by category", items: categoryTotals.income, color: "text-primary" },
                    { title: "Expense by category", items: categoryTotals.expense, color: "text-error" },
                ] as group}
                    <section class="flex min-h-0 flex-col gap-3 overflow-hidden rounded-xl border border-border bg-surface p-5">
                        <h2 class="text-sm font-medium text-ink">{group.title}</h2>
                        {#if group.items.length === 0}
                            <p class="text-sm text-muted">No {group.title.toLowerCase()} recorded.</p>
                        {:else}
                            <ul class="min-h-0 flex-1 overflow-y-auto">
                                {#each group.items as item (item.category)}
                                    <li class="mb-2 flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2 last:mb-0">
                                        <span class="min-w-0 truncate text-sm text-ink">
                                            {item.category}
                                        </span>
                                        <span class={`shrink-0 text-sm font-medium ${group.color}`}>
                                            {money.format(item.total)}
                                        </span>
                                    </li>
                                {/each}
                            </ul>
                        {/if}
                    </section>
                {/each}
            </div>
        {/if}
    </main>
</div>
