<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
    import TransactionForm from "@/components/transactions/TransactionForm.svelte";
    import Button from "@/components/ui/Button.svelte";
    import Pagination from "@/components/ui/Pagination.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import TransactionController from "@/actions/App/Http/Controllers/TransactionController";
    import { categories as categoriesRoute, expense, income } from "@/routes";
    import type {
        CategoryOption,
        TransactionFilters,
        TransactionPaginator,
        TransactionRow,
    } from "@/types/transaction";
    import { router, usePage } from "@inertiajs/svelte";

    let { type }: { type: "income" | "expense" } = $props();

    type PageProps = {
        transactions: TransactionPaginator;
        categories: CategoryOption[];
        filters: TransactionFilters;
        total: number;
    };

    const page = usePage<PageProps>();
    const transactions = $derived(page.props.transactions);
    const categories = $derived(page.props.categories);
    const filters = $derived(page.props.filters);
    const total = $derived(page.props.total);

    const isIncome = $derived(type === "income");
    const label = $derived(isIncome ? "Income" : "Expense");
    const labelLower = $derived(isIncome ? "income" : "expense");
    const baseUrl = $derived(isIncome ? income.url() : expense.url());
    const hasFilters = $derived(Object.keys(filters).length > 0);

    let formOpen = $state(false);
    let editing = $state<TransactionRow | null>(null);
    let requestError = $state("");

    const exportUrl = $derived(
        (isIncome
            ? TransactionController.exportIncome
            : TransactionController.exportExpense
        ).url({ query: queryFor(filters) }),
    );

    const money = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    });

    function queryFor(value: TransactionFilters) {
        const query: Record<string, string> = {};

        if (value.from) {
            query.from = value.from;
        }

        if (value.to) {
            query.to = value.to;
        }

        if (value.category) {
            query.category = String(value.category);
        }

        if (value.q) {
            query.q = value.q;
        }

        return query;
    }

    function openCreate() {
        editing = null;
        formOpen = true;
    }

    function openEdit(transaction: TransactionRow) {
        editing = transaction;
        formOpen = true;
    }

    function closeForm() {
        formOpen = false;
        editing = null;
    }

    function applyFilters(event: SubmitEvent) {
        event.preventDefault();
        requestError = "";

        const data = new FormData(event.currentTarget as HTMLFormElement);
        const query: Record<string, string> = {};

        data.forEach((value, key) => {
            const sanitized = String(value).trim();

            if (sanitized) {
                query[key] = sanitized;
            }
        });

        router.get(baseUrl, query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onError: () => (requestError = "We could not load these entries. Try again."),
        });
    }

    function resetFilters() {
        requestError = "";
        router.get(baseUrl, {}, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onError: () => (requestError = "We could not load these entries. Try again."),
        });
    }

    function destroy(transaction: TransactionRow) {
        if (!window.confirm(`Delete this ${labelLower} entry?`)) {
            return;
        }

        router.delete(TransactionController.destroy.url(transaction.id), {
            preserveScroll: true,
        });
    }
</script>

<AppHead title={label} />

<svelte:window
    onkeydown={(event) => {
        if (event.key === "Escape") {
            closeForm();
        }
    }}
/>

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="flex min-h-0 w-full flex-1 flex-col gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-tight text-ink">
                    {label}
                </h1>
                <p class="text-sm text-muted">
                    {transactions.total}
                    {transactions.total === 1 ? "entry" : "entries"}
                    {hasFilters ? "matching these filters" : "recorded"} · total
                    {money.format(total)}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href={exportUrl}
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-ink transition-colors hover:bg-background focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    Export CSV
                </a>
                <Button
                    onclick={openCreate}
                    disabled={categories.length === 0}
                >
                    New {labelLower}
                </Button>
            </div>
        </div>

        {#if requestError}
            <div class="rounded-lg border border-error/30 bg-error/10 p-4 text-sm text-error" role="alert">
                {requestError}
            </div>
        {/if}

        {#if categories.length === 0}
            <div
                class="grid gap-1 rounded-xl border border-border bg-surface p-6"
            >
                <h2 class="text-sm font-medium text-ink">No categories yet</h2>
                <p class="text-sm text-muted">
                    Add a {labelLower} category before recording an entry.
                    <a
                        href={categoriesRoute.url()}
                        class="font-medium text-primary underline underline-offset-2 hover:text-primary-hover"
                    >
                        Go to categories
                    </a>
                </p>
            </div>
        {/if}

        <form
            class="grid gap-4 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end"
            onsubmit={applyFilters}
        >
            <TextField
                label="From"
                name="from"
                type="date"
                value={filters.from ?? ""}
            />
            <TextField
                label="To"
                name="to"
                type="date"
                value={filters.to ?? ""}
            />
            <div class="grid gap-1.5">
                <label for="filter-category" class="text-sm font-medium text-ink">
                    Category
                </label>
                <select
                    id="filter-category"
                    name="category"
                    value={filters.category ? String(filters.category) : ""}
                    class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                >
                    <option value="">All categories</option>
                    {#each categories as category (category.id)}
                        <option value={String(category.id)}>
                            {category.name}
                        </option>
                    {/each}
                </select>
            </div>
            <TextField
                label="Notes contain"
                name="q"
                placeholder="Search notes..."
                value={filters.q ?? ""}
            />

            <div class="flex items-center gap-2">
                <Button type="submit" class="flex-1">Apply</Button>
                <Button
                    variant="secondary"
                    onclick={resetFilters}
                    disabled={!hasFilters}
                >
                    Reset
                </Button>
            </div>
        </form>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-border bg-surface">
            {#if transactions.data.length === 0}
                <div class="grid gap-2 p-6">
                    <h2 class="text-sm font-medium text-ink">
                        {hasFilters
                            ? "No entries match these filters"
                            : `No ${labelLower} entries yet`}
                    </h2>
                    <p class="text-sm text-muted">
                        {hasFilters
                            ? "Try a wider date range or clear the filters."
                            : `Once you record ${labelLower}, it will appear in this list.`}
                    </p>
                    {#if hasFilters}
                        <Button
                            variant="secondary"
                            onclick={resetFilters}
                            class="w-fit"
                        >
                            Clear filters
                        </Button>
                    {:else if categories.length > 0}
                        <Button onclick={openCreate} class="w-fit">
                            New {labelLower}
                        </Button>
                    {/if}
                </div>
            {:else}
                <div class="min-h-0 flex-1 overflow-auto">
                    <table class="w-full min-w-3xl text-left text-sm">
                        <thead class="text-xs text-muted">
                            <tr>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium">Time</th>
                                <th class="px-4 py-3 font-medium">Category</th>
                                <th class="px-4 py-3 font-medium">Notes</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Amount
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each transactions.data as transaction (transaction.id)}
                                <tr class="border-t border-border">
                                    <td class="px-4 py-3 whitespace-nowrap text-ink">
                                        {transaction.date}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-muted">
                                        {transaction.time ?? "-"}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-ink">
                                        {transaction.category ?? "Uncategorized"}
                                    </td>
                                    <td
                                        class="max-w-xs truncate px-4 py-3 text-muted"
                                        title={transaction.notes ?? ""}
                                    >
                                        {transaction.notes || "-"}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-medium whitespace-nowrap"
                                        class:text-primary={isIncome}
                                        class:text-error={!isIncome}
                                    >
                                        {isIncome ? "+" : "-"}
                                        {money.format(transaction.amount)}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-1">
                                            <button
                                                type="button"
                                                onclick={() => openEdit(transaction)}
                                                aria-label="Edit {labelLower} entry"
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-background hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                onclick={() => destroy(transaction)}
                                                aria-label="Delete {labelLower} entry"
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-error/10 hover:text-error focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination
                    links={transactions.links}
                    from={transactions.from}
                    to={transactions.to}
                    total={transactions.total}
                />
            {/if}
        </div>
    </main>
</div>

{#if formOpen}
    {#key editing?.id ?? "new"}
        <TransactionForm
            {type}
            {categories}
            transaction={editing}
            onclose={closeForm}
        />
    {/key}
{/if}
