<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import Button from "@/components/ui/Button.svelte";
    import { Form } from "@inertiajs/svelte";
    import { logout } from "@/routes";
</script>

<AppHead title="Home">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
</AppHead>
<div>
    <h1>This will be Home Page</h1>
    <Form {...logout.form()} class="mt-4">
        {#snippet children({ processing })}
            <Button type="submit" variant="secondary" loading={processing}>
                {processing ? "Logging out..." : "Log out"}
            </Button>
        {/snippet}
    </Form>
</div>
