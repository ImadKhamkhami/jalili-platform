<script setup lang="ts">
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">

    <!-- الشعار أولا -->
    <div class="flex flex-col items-center gap-2">
        <img
            src="/images/jalili-logo.png"
            alt="Jalili Logo"
            class="w-28 h-auto opacity-95"
        />
    </div>

    <!-- ثم العنوان والوصف تحت الشعار -->
    <div class="space-y-2 text-center mt-2">
        <h1 class="text-xl font-medium">{{ title }}</h1>
        <p class="text-center text-sm text-muted-foreground">
            {{ description }}
        </p>
    </div>
</div>


                <slot />
            </div>
        </div>
    </div>
</template>