<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'العملاء', href: '/clients' },
];
</script>

<template>
  <Head title="العملاء" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h2 class="text-2xl font-bold mb-4">قائمة الزبناء</h2>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-muted">قائمة المشترين (Clients) سيتم عرضها هنا.</p>
      </div>
    </div>
  </AppLayout>
</template>
