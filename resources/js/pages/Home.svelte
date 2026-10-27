<script lang="ts">
    import { Link, router } from "@inertiajs/svelte";
    import { expense, income, home } from "@/routes";
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
    import Pagination from "@/components/ui/Pagination.svelte";

    type Summary = {
        balance: number;
        month_income: number;
        month_expense: number;
        month_label: string;
    };

    type RecentTransaction = {
        id: number;
        type: "income" | "expense";
        amount: number;
        date: string;
        category: string | null;
        notes: string | null;
    };

    let {
        summary,
        recent_transactions: recentTransactions,
        filters,
        categories: categoryOptions,
    }: {
        summary: Summary;
        recent_transactions: {
            data: RecentTransaction[];
            links: {
                url: string | null;
                label: string;
                active: boolean;
            }[];
            from: number | null;
            to: number | null;
            total: number;
        };
        filters: {
            from?: string;
            to?: string;
            category?: number;
            q?: string;
            type?: "income" | "expense";
        };
        categories: { id: number; name: string; type: "income" | "expense" }[];
    } = $props();

    const money = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    });

    const hasTransactions = $derived(recentTransactions.data.length > 0);
    const hasFilters = $derived(Object.keys(filters).length > 0);

    function formatDate(value: string) {
        return new Intl.DateTimeFormat("en", {
            day: "numeric",
            month: "short",
            year: "numeric",
        }).format(new Date(`${value}T00:00:00`));
    }

    function applyFilters(event: SubmitEvent) {
        event.preventDefault();

        const data = new FormData(event.currentTarget as HTMLFormElement);
        const query: Record<string, string> = {};

        data.forEach((value, key) => {
            const sanitized = String(value).trim();

            if (sanitized) {
                query[key] = sanitized;
            }
        });

        router.get(home.url(), query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    function resetFilters() {
        router.get(home.url(), {}, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

</script>

<AppHead title="Dashboard" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="grid w-full min-h-0 flex-1 content-start gap-6 overflow-y-auto p-6 md:grid-rows-[auto_auto_auto_minmax(0,1fr)] lg:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-tight text-ink">
                    Dashboard
                </h1>
                <p class="text-sm text-muted">
                    Your current money overview.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link
                    href={income.url()}
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    View income
                </Link>
                <Link
                    href={expense.url()}
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-ink transition-colors hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    View expenses
                </Link>
            </div>
        </div>

        <section class="grid gap-4 lg:grid-cols-[1.35fr_1fr_1fr]">
            <div class="grid gap-8 rounded-xl bg-primary p-6 text-primary-ink">
                <div class="grid gap-1">
                    <p class="text-sm opacity-80">Current balance</p>
                    <p class="break-words text-3xl font-semibold tracking-tight">
                        {money.format(summary.balance)}
                    </p>
                </div>
            </div>
            <div class="grid gap-4 rounded-xl border border-border bg-surface p-5">
                <div class="grid gap-1">
                    <p class="text-sm text-muted">Income</p>
                    <p class="text-xl font-semibold text-income">
                        +{money.format(summary.month_income)}
                    </p>
                </div>
                <p class="text-xs text-muted">{summary.month_label}</p>
            </div>
            <div class="grid gap-4 rounded-xl border border-border bg-surface p-5">
                <div class="grid gap-1">
                    <p class="text-sm text-muted">Expenses</p>
                    <p class="text-xl font-semibold text-expense">
                        -{money.format(summary.month_expense)}
                    </p>
                </div>
                <p class="text-xs text-muted">{summary.month_label}</p>
            </div>
        </section>

        <form
            class="grid gap-4 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-6 lg:items-end"
            onsubmit={applyFilters}
        >
            <div class="grid gap-1.5">
                <label for="dashboard-from" class="text-sm font-medium text-ink">From</label>
                <input
                    id="dashboard-from"
                    name="from"
                    type="date"
                    value={filters.from ?? ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                />
            </div>
            <div class="grid gap-1.5">
                <label for="dashboard-to" class="text-sm font-medium text-ink">To</label>
                <input
                    id="dashboard-to"
                    name="to"
                    type="date"
                    value={filters.to ?? ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                />
            </div>
            <div class="grid gap-1.5">
                <label for="dashboard-type" class="text-sm font-medium text-ink">Type</label>
                <select
                    id="dashboard-type"
                    name="type"
                    value={filters.type ?? ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                >
                    <option value="">All types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div class="grid gap-1.5">
                <label for="dashboard-category" class="text-sm font-medium text-ink">Category</label>
                <select
                    id="dashboard-category"
                    name="category"
                    value={filters.category ? String(filters.category) : ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                >
                    <option value="">All categories</option>
                    {#each categoryOptions as category (category.id)}
                        <option value={String(category.id)}>{category.name}</option>
                    {/each}
                </select>
            </div>
            <div class="grid gap-1.5">
                <label for="dashboard-notes" class="text-sm font-medium text-ink">Notes contain</label>
                <input
                    id="dashboard-notes"
                    name="q"
                    type="text"
                    placeholder="Search notes..."
                    value={filters.q ?? ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink placeholder:text-muted focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                />
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-ink transition-colors hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    Apply
                </button>
                <button
                    type="button"
                    onclick={resetFilters}
                    disabled={!hasFilters}
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Reset
                </button>
            </div>
        </form>

        {#if hasTransactions}
            <section class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)_auto] gap-4 overflow-hidden rounded-xl border border-border bg-surface p-5">
                <div class="grid gap-1">
                    <div class="grid gap-1">
                        <h2 class="text-sm font-medium text-ink">Recent transactions</h2>
                        <p class="text-xs text-muted">Your latest recorded entries.</p>
                    </div>
                </div>
                <div class="min-h-0 overflow-y-auto pr-1">
                    <div class="grid gap-2">
                    {#each recentTransactions.data as transaction (transaction.id)}
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border py-3 first:border-t-0 first:pt-0 last:pb-0">
                            <div class="grid min-w-0 gap-1">
                                <span class="truncate text-sm font-medium text-ink">
                                    {transaction.category ?? "Uncategorized"}
                                </span>
                                <span class="truncate text-xs text-muted">
                                    {transaction.notes || formatDate(transaction.date)}
                                </span>
                            </div>
                            <span
                                class:text-income={transaction.type === "income"}
                                class:text-expense={transaction.type === "expense"}
                                class="shrink-0 text-sm font-medium"
                            >
                                {transaction.type === "income" ? "+" : "-"}
                                {money.format(transaction.amount)}
                            </span>
                        </div>
                    {/each}
                    </div>
                </div>
                <Pagination
                    links={recentTransactions.links}
                    from={recentTransactions.from}
                    to={recentTransactions.to}
                    total={recentTransactions.total}
                />
            </section>
        {:else}
            <section class="grid gap-3 rounded-xl border border-border bg-surface p-6">
                <div class="grid gap-1">
                    <h2 class="text-sm font-medium text-ink">Nothing here yet</h2>
                    <p class="text-sm text-muted">
                        Add an income or expense entry to see your overview grow.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        href={income.url()}
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-ink transition-colors hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        Add income
                    </Link>
                    <Link
                        href={expense.url()}
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        Add expense
                    </Link>
                </div>
            </section>
        {/if}

    </main>
</div>
