<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import AppSidebar from "@/components/AppSidebar.svelte";
    import Button from "@/components/ui/Button.svelte";
    import TextField from "@/components/ui/TextField.svelte";
    import SettingsController from "@/actions/App/Http/Controllers/SettingsController";
    import { cn } from "@/lib/utils";
    import { Form, usePage } from "@inertiajs/svelte";

    const page = usePage();
    const account = page.props.auth.user;

    let username = $state(account.username ?? "");
    let fullName = $state(account.full_name ?? "");
    let email = $state(account.email ?? "");
    let initialBalance = $state(String(account.initial_balance ?? 0));

    const accents = [
        { name: "rosewater", label: "Rosewater" },
        { name: "flamingo", label: "Flamingo" },
        { name: "pink", label: "Pink" },
        { name: "mauve", label: "Mauve" },
        { name: "red", label: "Red" },
        { name: "maroon", label: "Maroon" },
        { name: "peach", label: "Peach" },
        { name: "yellow", label: "Yellow" },
        { name: "green", label: "Green" },
        { name: "teal", label: "Teal" },
        { name: "sky", label: "Sky" },
        { name: "sapphire", label: "Sapphire" },
        { name: "blue", label: "Blue" },
        { name: "lavender", label: "Lavender" },
    ];

    let accent = $state(
        typeof document !== "undefined"
            ? (document.documentElement.dataset.accent ?? "green")
            : "green",
    );

    const accentLabel = $derived(
        accents.find((item) => item.name === accent)?.label ?? "Green",
    );

    function chooseAccent(name: string) {
        accent = name;
        document.documentElement.dataset.accent = name;
        localStorage.setItem("accent", name);
    }
</script>

<AppHead title="Settings" />

<div class="flex h-dvh flex-col overflow-hidden md:flex-row">
    <AppSidebar />

    <main class="grid w-full flex-1 content-start gap-6 overflow-y-auto p-6 lg:p-8">
        <div class="grid gap-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">
                Settings
            </h1>
            <p class="text-sm text-muted">
                Account details, appearance, and password.
            </p>
        </div>

        <Form
            {...SettingsController.update.form()}
            resetOnSuccess={false}
            class="grid gap-4 rounded-xl border border-border bg-surface p-6"
        >
            {#snippet children({ errors, processing, recentlySuccessful })}
                <div class="grid gap-1">
                    <h2 class="text-sm font-medium text-ink">Profile</h2>
                    <p class="text-xs text-muted">
                        Used for signing in and shown in the app.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <TextField
                        label="Username"
                        name="username"
                        autocomplete="username"
                        required
                        bind:value={username}
                        error={errors.username}
                    />
                    <TextField
                        label="Full name"
                        name="full_name"
                        autocomplete="name"
                        required
                        bind:value={fullName}
                        error={errors.full_name}
                    />
                    <TextField
                        label="Email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        bind:value={email}
                        error={errors.email}
                    />
                    <TextField
                        label="Initial balance"
                        name="initial_balance"
                        placeholder="0"
                        required
                        bind:value={initialBalance}
                        error={errors.initial_balance}
                    />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" loading={processing}>
                        {processing ? "Saving..." : "Save profile"}
                    </Button>
                    {#if recentlySuccessful}
                        <span class="text-xs text-primary">Saved</span>
                    {/if}
                </div>
            {/snippet}
        </Form>

        <section
            class="grid gap-4 rounded-xl border border-border bg-surface p-6"
        >
            <div class="grid gap-1">
                <h2 class="text-sm font-medium text-ink">Accent color</h2>
                <p class="text-xs text-muted">
                    Catppuccin palette, saved in this browser. Current:
                    {accentLabel}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                {#each accents as item (item.name)}
                    <button
                        type="button"
                        onclick={() => chooseAccent(item.name)}
                        aria-label="Use {item.label} accent"
                        aria-pressed={accent === item.name}
                        title={item.label}
                        class={cn(
                            "size-8 rounded-full border border-black/10 transition-transform dark:border-white/10",
                            "hover:scale-110 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
                            accent === item.name &&
                                "ring-2 ring-ink ring-offset-2 ring-offset-surface",
                        )}
                        style="background-color: var(--swatch-{item.name})"
                    ></button>
                {/each}
            </div>
        </section>

        <Form
            {...SettingsController.updatePassword.form()}
            resetOnSuccess
            class="grid gap-4 rounded-xl border border-border bg-surface p-6"
        >
            {#snippet children({ errors, processing, recentlySuccessful })}
                <div class="grid gap-1">
                    <h2 class="text-sm font-medium text-ink">Password</h2>
                    <p class="text-xs text-muted">
                        Requires your current password.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <TextField
                        label="Current password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        error={errors.current_password}
                    />
                    <div class="hidden sm:block"></div>
                    <TextField
                        label="New password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        error={errors.password}
                    />
                    <TextField
                        label="Confirm new password"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        error={errors.password_confirmation}
                    />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" loading={processing}>
                        {processing ? "Updating..." : "Update password"}
                    </Button>
                    {#if recentlySuccessful}
                        <span class="text-xs text-primary">Updated</span>
                    {/if}
                </div>
            {/snippet}
        </Form>
    </main>
</div>
