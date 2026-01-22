<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'الشركات', href: '/companies' },
];
</script>

<template>
  <Head title="الشركات" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h2 class="text-2xl font-bold mb-4">قائمة الشركات</h2>
      <!-- مثال عرض جدول بسيط -->
      <div class="bg-white rounded-lg shadow p-4">
        <table class="w-full table-auto text-sm">
          <thead>
            <tr class="text-left">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">اسم الشركة</th>
              <th class="px-3 py-2">الهاتف</th>
              <th class="px-3 py-2">الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <!-- placeholder rows -->
            <tr>
              <td class="px-3 py-2">1</td>
              <td class="px-3 py-2">شركة مثال</td>
              <td class="px-3 py-2">+212 6x xxx xxxx</td>
              <td class="px-3 py-2">عرض | تعديل</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
