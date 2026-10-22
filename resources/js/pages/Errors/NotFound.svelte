<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import ThemeToggle from "@/components/ThemeToggle.svelte";
    import { home } from "@/routes";
</script>

<AppHead title="Page not found" />

<div class="relative grid min-h-dvh place-items-center px-4 py-8">
    <div class="absolute top-5 right-5">
        <ThemeToggle />
    </div>

    <div class="grid max-w-sm justify-items-stretch gap-2">
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-ink">
            Page not found
        </h1>
        <p class="text-sm text-muted">
            The page you're looking for doesn't exist or was moved.
        </p>
        <a
            href={home.url()}
            class="mt-3 w-fit font-medium text-primary underline underline-offset-2 hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
        >
            Back to the homepage
        </a>
    </div>
</div>
