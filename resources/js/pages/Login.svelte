<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import Button from "@/components/ui/Button.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import ThemeToggle from "@/components/ThemeToggle.svelte";
    import { Form } from "@inertiajs/svelte";
    import { register } from "@/routes";
</script>

<AppHead title="Log in" />

<Form
    action="/login"
    method="post"
    resetOnSuccess={false}
    class="relative flex min-h-dvh items-center justify-center px-4 py-8"
>
    {#snippet children({ errors, processing })}
        <div class="absolute top-5 right-5">
            <ThemeToggle />
        </div>

        <div class="grid w-full max-w-sm justify-items-stretch gap-6">
            <div class="grid gap-1.5">
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-ink">
                    Log in
                </h1>
                <p class="text-sm text-muted">
                    Enter your credentials to access your account.
                </p>
            </div>

            <div
                class="grid gap-4 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                <TextField
                    label="Email or username"
                    name="login"
                    autocomplete="username"
                    placeholder="Enter your email or username..."
                    required
                    error={errors.login}
                />
                <TextField
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your password..."
                    required
                    error={errors.password}
                />

                <Button type="submit" loading={processing}>
                    {processing ? "Logging in..." : "Log in"}
                </Button>
            </div>

            <p class="text-center text-sm text-muted">
                Don't have an account yet?
                <a
                    href={register.url()}
                    class="font-medium text-primary underline underline-offset-2 hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    Create one
                </a>
            </p>
        </div>
    {/snippet}
</Form>
