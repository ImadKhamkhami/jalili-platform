<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'المشاريع', href: '/projects' },
];
</script>

<template>
  <Head title="المشاريع" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h2 class="text-2xl font-bold mb-4">قائمة المشاريع</h2>
      <div class="bg-white rounded-lg shadow p-4">
        <!-- هنا تعرض مشاريعك (عمارات/تجزئات) -->
        <p class="text-muted">هنا سيعرض جدول المشاريع — مثال placeholder.</p>
      </div>
    </div>
  </AppLayout>
</template>
