<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import Button from "@/components/ui/Button.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import ThemeToggle from "@/components/ThemeToggle.svelte";
    import { Form } from "@inertiajs/svelte";
    import { login } from "@/routes";
</script>

<AppHead title="Register" />

<Form
    action="/register"
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
                    Register
                </h1>
                <p class="text-sm text-muted">
                    Create an account to get started.
                </p>
            </div>

            <div
                class="grid gap-4 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                <TextField
                    label="Username"
                    name="username"
                    autocomplete="username"
                    placeholder="Create a username..."
                    required
                    error={errors.username}
                />
                <TextField
                    label="Full name"
                    name="full_name"
                    autocomplete="name"
                    placeholder="Enter your full name..."
                    required
                    error={errors.full_name}
                />
                <TextField
                    label="Email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="Enter your email..."
                    required
                    error={errors.email}
                />
                <TextField
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Create a password..."
                    required
                    error={errors.password}
                />
                <TextField
                    label="Confirm password"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Confirm your password..."
                    required
                    error={errors.password_confirmation}
                />

                <Button type="submit" loading={processing}>
                    {processing ? "Creating account..." : "Create account"}
                </Button>
            </div>

            <p class="text-center text-sm text-muted">
                Already have an account?
                <a
                    href={login.url()}
                    class="font-medium text-primary underline underline-offset-2 hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    Log in
                </a>
            </p>
        </div>
    {/snippet}
</Form>
