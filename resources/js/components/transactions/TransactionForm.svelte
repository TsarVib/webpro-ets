<script lang="ts">
    import Button from "@/components/ui/Button.svelte";
    import SelectField from "@/components/ui/SelectField.svelte";
    import TextAreaField from "@/components/ui/TextAreaField.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import TransactionController from "@/actions/App/Http/Controllers/TransactionController";
    import type { CategoryOption, TransactionRow } from "@/types/transaction";
    import { useForm } from "@inertiajs/svelte";
    import { tick } from "svelte";
    import { untrack } from "svelte";

    let {
        type,
        categories,
        transaction = null,
        onclose,
    }: {
        type: "income" | "expense";
        categories: CategoryOption[];
        transaction?: TransactionRow | null;
        onclose: () => void;
    } = $props();

    const editing = $derived(transaction !== null);
    const label = $derived(type === "income" ? "income" : "expense");
    let dialog = $state<HTMLDivElement>();

    $effect(() => {
        const element = dialog;

        if (element) {
            tick().then(() => element.querySelector<HTMLElement>("input, select, textarea, button")?.focus());
        }
    });

    const today = () => {
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");

        return `${now.getFullYear()}-${month}-${day}`;
    };

    const currentTime = () => {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, "0");
        const minutes = String(now.getMinutes()).padStart(2, "0");

        return `${hours}:${minutes}`;
    };

    const form = useForm(
        untrack(() => ({
            type,
            category_id: transaction ? String(transaction.category_id) : "",
            amount: transaction ? String(transaction.amount) : "",
            notes: transaction?.notes ?? "",
            date: transaction?.date ?? today(),
            time: transaction?.time?.slice(0, 5) ?? currentTime(),
        })),
    );

    const categoryOptions = $derived(
        categories.map((category) => ({
            value: String(category.id),
            label: category.name,
        })),
    );

    function submit(event: SubmitEvent) {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => onclose(),
        };

        if (transaction) {
            form.patch(TransactionController.update.url(transaction.id), options);

            return;
        }

        form.post(TransactionController.store.url(), options);
    }
</script>

<div
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10"
>
    <button
        type="button"
        class="fixed inset-0 cursor-default"
        aria-label="Close form"
        onclick={onclose}
    ></button>

    <div
    bind:this={dialog}
    role="dialog"
        aria-modal="true"
        aria-labelledby="transaction-form-title"
        class="relative z-10 w-full max-w-lg rounded-xl border border-border bg-surface p-6 shadow-xl"
    >
        <div class="mb-5 flex items-start justify-between gap-4">
            <div class="grid gap-1">
                <h2
                    id="transaction-form-title"
                    class="text-lg font-semibold tracking-tight text-ink"
                >
                    {editing ? "Edit" : "New"}
                    {label}
                </h2>
                <p class="text-xs text-muted">
                    {editing
                        ? "Update the details of this entry."
                        : `Record money that ${type === "income" ? "came in" : "went out"}.`}
                </p>
            </div>

            <button
                type="button"
                onclick={onclose}
                class="rounded-lg px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-background hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
            >
                Close
            </button>
        </div>

        <form class="grid gap-4" onsubmit={submit}>
            <TextField
                label="Amount"
                name="amount"
                type="number"
                min={1}
                step={1}
                placeholder="0"
                required
                bind:value={form.amount}
                error={form.errors.amount}
            />

            <SelectField
                label="Category"
                name="category_id"
                required
                options={categoryOptions}
                bind:value={form.category_id}
                error={form.errors.category_id}
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <TextField
                    label="Date"
                    name="date"
                    type="date"
                    required
                    bind:value={form.date}
                    error={form.errors.date}
                />
                <TextField
                    label="Time"
                    name="time"
                    type="time"
                    bind:value={form.time}
                    error={form.errors.time}
                />
            </div>

            <TextAreaField
                label="Notes"
                name="notes"
                rows={3}
                placeholder="What was this for?"
                bind:value={form.notes}
                error={form.errors.notes}
            />

            <div class="flex items-center justify-end gap-2 pt-1">
                <Button variant="secondary" onclick={onclose}>
                    Cancel
                </Button>
                <Button type="submit" loading={form.processing}>
                    {form.processing
                        ? "Saving..."
                        : editing
                          ? "Save changes"
                          : `Add ${label}`}
                </Button>
            </div>
        </form>
    </div>
</div>
