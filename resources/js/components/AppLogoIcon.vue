<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>

    <img
        src="/images/logo-jalili.jpg"
        alt="Jalili Logo"
        class="w-8 h-auto"
    />


</template>
