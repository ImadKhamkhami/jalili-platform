<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

// حل سريع ومضمون:
const isOpen = true;

// أو إذا شاركت الخاصية من Laravel:
// const isOpen = usePage().props.sidebarOpen ?? true;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col ">
        <slot />
    </div>

    <SidebarProvider :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
