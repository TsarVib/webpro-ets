<script lang="ts">
    import type { Snippet } from "svelte";

    let {
        title = "",
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

</script>

<svelte:head>
    <title>{title}</title>
    {@render children?.()}
</svelte:head>
