<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { reactive } from 'vue'

/* ================= PROPS ================= */
const props = defineProps({
  customers: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

/* ================= FILTER FORM ================= */
const form = reactive({
  search: props.filters.search ?? '',
})

function searchCustomers() {
  router.get('/customers', form, {
    preserveState: true,
    replace: true,
  })
}

function resetSearch() {
  form.search = ''
  router.get('/customers', {}, { replace: true })
}
</script>

<template>
<AppLayout title="الزبناء">
  <div class="p-8">

    <!-- ================= العنوان ================= -->
    <div class="mb-6 text-right">
      <h1 class="text-2xl font-bold text-green-700 flex items-center gap-2">
        البحـــث عن زبـــون
      </h1>
    </div>

    <!-- ================= بطاقة البحث (نفس الفلاتر) ================= -->
    <div class="bg-white rounded-2xl shadow p-5 mb-8">
      <div class="flex flex-wrap items-end gap-4">

        <!-- حقل البحث -->
        <div class="flex-1 min-w-[300px]">
          <label class="text-s font-bold text-gray-500 mb-1 block">
            اسم الزبون او رقم البطاقة الوطنية
          </label>
          <input
            v-model="form.search"
            type="text"
            placeholder=".............................................................."
            class="w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-green-200"
          />
        </div>

<!-- الأزرار -->
<div class="flex items-center gap-3">

  <!-- بحث -->
  <button
    @click="searchCustomers"
    title="بحث"
    class="group h-10 w-10 flex items-center justify-center
           bg-green-600 text-white rounded-xl
           hover:bg-green-700 active:scale-95
           transition-all shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:scale-110 transition"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
      <path stroke-linecap="round"
            stroke-linejoin="round"
            d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0z" />
    </svg>
  </button>

  <!-- إعادة -->
  <button
    @click="resetSearch"
    title="إعادة"
    class="group h-10 w-10 flex items-center justify-center
           bg-white border border-gray-200 text-gray-600
           rounded-xl hover:bg-gray-100
           active:scale-95 transition-all shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:rotate-180 transition"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
      <path stroke-linecap="round"
            stroke-linejoin="round"
            d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0 0 14-7M19 5a9 9 0 0 0-14 7" />
    </svg>
  </button>

</div>


      </div>
    </div>

    <!-- قبل البحث -->
<div
  v-if="!form.search"
  class="bg-white rounded-2xl shadow p-10 text-center text-gray-400"
>
  أدخل اسم الزبون أو رقم البطاقة ثم اضغط على زر البحث
</div>


    <!-- ================= جدول النتائج ================= -->
    <div v-if="form.search" class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="w-full text-sm text-center">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-4 py-4">#</th>
            <th class="px-4 py-4 text-right">الاسم</th>
            <th class="px-4 py-4">رقم البطاقة</th>
            <th class="px-4 py-4">الهاتف</th>
            <th class="px-4 py-4">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(customer, index) in customers"
            :key="customer.id"
            class="border-t hover:bg-gray-50 transition">

            <td class="px-4 py-3">
              {{ index + 1 }}
            </td>

            <td class="px-4 py-3 text-right font-semibold">
              {{ customer.name }}
            </td>

            <td class="px-4 py-3 font-mono">
              {{ customer.national_id }}
            </td>

            <td class="px-4 py-3">
              {{ customer.phone || '-' }}
            </td>

            <!-- الإجراءات -->
            <td class="px-4 py-3">
              <div class="flex justify-center gap-3">

                <!-- عرض الملف -->
                 <Link
  :href="`/customers/${customer.id}`"
  class="inline-flex items-center gap-2
         px-3 py-1.5
         rounded-lg
         bg-green-50
         text-green-700
         hover:bg-green-100 hover:text-green-800
         transition font-medium text-sm"
>
  <!-- Eye Icon -->

  <svg xmlns="http://www.w3.org/2000/svg"
       class="w-5 h-5"
       fill="none"
       viewBox="0 0 24 24"
       stroke="currentColor"
       stroke-width="2">
    <path stroke-linecap="round"
          stroke-linejoin="round"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    <path stroke-linecap="round"
          stroke-linejoin="round"
          d="M2.458 12C3.732 7.943 7.523 5 12 5
             c4.478 0 8.268 2.943 9.542 7
             -1.274 4.057-5.064 7-9.542 7
             -4.477 0-8.268-2.943-9.542-7z" />
  </svg>
     <span>عرض الملف</span>
</Link>

              </div>
            </td>
          </tr>

          <!-- لا نتائج -->
          <tr v-if="customers.length === 0">
            <td colspan="5" class="py-10 text-gray-400">
              لا توجد نتائج 
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</AppLayout>
</template>
