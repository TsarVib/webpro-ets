<script lang="ts">
    import CategoryController from "@/actions/App/Http/Controllers/CategoryController";
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
    import Button from "@/components/ui/Button.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import { router, usePage } from "@inertiajs/svelte";
    import { Form } from "@inertiajs/svelte";

    type Category = {
        id: number;
        name: string;
        type: "income" | "expense";
        transactions_count: number;
    };

    const entries = (count: number) =>
        count === 0 ? "No entries" : count === 1 ? "1 entry" : `${count} entries`;

    let { categories = [] }: { categories: Category[] } = $props();

    const page = usePage<{ errors: Record<string, string> }>();
    const incomeCategories = $derived(
        categories.filter((category) => category.type === "income"),
    );
    const expenseCategories = $derived(
        categories.filter((category) => category.type === "expense"),
    );

    let formOpen = $state(false);
    let editing = $state<Category | null>(null);
    let name = $state("");
    let type = $state<"income" | "expense">("expense");

    function openCreate() {
        editing = null;
        name = "";
        type = "expense";
        formOpen = true;
    }

    function openEdit(category: Category) {
        editing = category;
        name = category.name;
        type = category.type;
        formOpen = true;
    }

    function closeForm() {
        formOpen = false;
        editing = null;
    }

    function destroy(category: Category) {
        if (!window.confirm(`Delete the ${category.name} category?`)) {
            return;
        }

        router.delete(CategoryController.destroy.url(category.id), {
            preserveScroll: true,
        });
    }
</script>

<AppHead title="Categories" />

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
                    Categories
                </h1>
                <p class="text-sm text-muted">
                    Organize income and expense entries for this account.
                </p>
            </div>
            <Button onclick={openCreate}>Add category</Button>
        </div>

        {#if page.props.errors?.category}
            <div class="rounded-lg border border-error/30 bg-error/10 p-4 text-sm text-error" role="alert">
                {page.props.errors.category}
            </div>
        {/if}

        {#if categories.length === 0}
            <div class="grid gap-2 rounded-xl border border-border bg-surface p-6">
                <h2 class="text-sm font-medium text-ink">No categories yet</h2>
                <p class="text-sm text-muted">
                    Create your first category to use it when recording an entry.
                </p>
                <Button onclick={openCreate} class="w-fit">Create category</Button>
            </div>
        {:else}
            <div class="grid min-h-0 flex-1 gap-6 sm:grid-cols-2">
                {#each [
                    { title: "Income", items: incomeCategories },
                    { title: "Expense", items: expenseCategories },
                ] as group}
                    <section class="flex min-h-0 flex-col gap-3 overflow-hidden rounded-xl border border-border bg-surface p-5">
                        <h2 class="text-sm font-medium text-ink">{group.title}</h2>
                        {#if group.items.length === 0}
                            <p class="text-sm text-muted">No {group.title.toLowerCase()} categories.</p>
                        {:else}
                            <ul class="min-h-0 flex-1 overflow-y-auto">
                                {#each group.items as category (category.id)}
                                    <li class="mb-2 flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2 last:mb-0">
                                        <span class="grid min-w-0">
                                            <span class="truncate text-sm text-ink">{category.name}</span>
                                            <span class="text-xs text-muted">{entries(category.transactions_count)}</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-1">
                                            <button
                                                type="button"
                                                onclick={() => openEdit(category)}
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-muted hover:bg-background hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                onclick={() => destroy(category)}
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-muted hover:bg-error/10 hover:text-error focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                            >
                                                Delete
                                            </button>
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

{#if formOpen}
    <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10">
        <button
            type="button"
            class="fixed inset-0 cursor-default"
            aria-label="Close category form"
            onclick={closeForm}
        ></button>

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="category-form-title"
            class="relative z-10 grid w-full max-w-lg gap-5 rounded-xl border border-border bg-surface p-6 shadow-xl"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="grid gap-1">
                    <h2 id="category-form-title" class="text-lg font-semibold tracking-tight text-ink">
                        {editing ? "Edit category" : "Add category"}
                    </h2>
                    <p class="text-xs text-muted">
                        Choose a name and the type of entries it will organize.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={closeForm}
                    class="rounded-lg px-2 py-1 text-xs font-medium text-muted hover:bg-background hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    Close
                </button>
            </div>

            <Form
                {...(editing
                    ? CategoryController.update.form(editing.id)
                    : CategoryController.store.form())}
                resetOnSuccess={false}
                class="grid gap-4"
                onsubmit={() => undefined}
            >
                {#snippet children({ errors, processing })}
                    <TextField
                        label="Name"
                        name="name"
                        placeholder="For example, Rent"
                        required
                        bind:value={name}
                        error={errors.name}
                    />
                    <div class="grid gap-1.5">
                        <label for="category-type" class="text-sm font-medium text-ink">Type</label>
                        <select
                            id="category-type"
                            name="type"
                            bind:value={type}
                            class="h-10 w-full rounded-lg border border-border bg-surface px-3 text-sm text-ink focus:outline-2 focus:outline-offset-2 focus:outline-ring"
                        >
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                        {#if errors.type}
                            <p class="text-xs text-error" role="alert">{errors.type}</p>
                        {/if}
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="secondary" onclick={closeForm}>Cancel</Button>
                        <Button type="submit" loading={processing}>
                            {processing ? "Saving..." : editing ? "Save changes" : "Add category"}
                        </Button>
                    </div>
                {/snippet}
            </Form>
        </div>
    </div>
{/if}
