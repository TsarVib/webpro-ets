import { createInertiaApp } from '@inertiajs/svelte';

void createInertiaApp({
    title: (title) => title ?? '',
    progress: {
        color: '#40a02b',
    },
});
