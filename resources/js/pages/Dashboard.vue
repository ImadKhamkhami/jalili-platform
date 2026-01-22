<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import { ref } from 'vue'


const props = defineProps({
  stats: Object,
  lastPayments: Array,
})


/* ================= SEARCH FORM (نفس customers) ================= */
const form = reactive({
  search: '',
})

function submitSearch() {
  if (!form.search.trim()) return

  router.get('/customers', {
    search: form.search,
  })
}

function resetSearch() {
  form.search = ''
}

/* ======================================================== */
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value ?? 0)
}
</script>

<template>
<AppLayout title="الرئيسية">

  <!-- نفس padding صفحة الدفوعات -->
  <div class="p-8">

    <!-- ================= العنوان ================= -->
    <div class="mb-6 text-right">
      <h1 class="text-3xl font-bold text-green-700">
        الرئيسية
      </h1>
    </div>

<!-- ================= بطاقة البحث (مطابقة customers) ================= -->
<div class="bg-white rounded-2xl shadow p-5 mb-8">
  <div class="flex flex-wrap items-end gap-4">

    <!-- حقل البحث -->
    <div class="flex-1 min-w-[300px]">
      <label class="text-s font-bold text-gray-500 mb-1 block">
        اسم الزبون أو رقم البطاقة الوطنية
      </label>

      <input
        v-model="form.search"
        type="text"
        placeholder=".............................................................."
        class="w-full border rounded-xl px-4 py-2
               focus:ring-2 focus:ring-green-200"
        @keyup.enter="submitSearch"
      />
    </div>

    <!-- الأزرار -->
    <div class="flex items-center gap-3">

      <!-- بحث -->
      <button
        @click="submitSearch"
        title="بحث"
        class="group h-10 w-10 flex items-center justify-center
               bg-green-600 text-white rounded-xl
               hover:bg-green-700 active:scale-95
               transition-all shadow-sm"
      >
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
               active:scale-95 transition-all shadow-sm"
      >
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

    <!-- ================= الإحصائيات (نفس cards) ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

      <div class="bg-white rounded-2xl shadow p-5">
        <div class="text-s text-gray-500 mb-1 font-bold">الشركات</div>
        <div class="text-2xl font-bold">
          {{ stats.companies }}
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-5">
        <div class="text-s text-gray-500 mb-1 font-bold">المشاريع</div>
        <div class="text-2xl font-bold">
          {{ stats.projects }}
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-5">
        <div class="text-s text-gray-500 mb-1 font-bold">الزبناء</div>
        <div class="text-2xl font-bold">
          {{ stats.customers }}
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-5">
        <div class="text-s text-gray-500 mb-1 font-bold">
          إجمالي دفوعات اليوم
        </div>
        <div class="text-2xl font-bold text-green-600">
          {{ formatMoney(stats.payments_sum) }} DH
        </div>
      </div>

    </div>

<!-- ================= آخر الدفوعات (نفس جدول صفحة الدفوعات) ================= -->
<div class="bg-white rounded-2xl shadow overflow-x-auto">

  <div class="px-5 py-4 border-b">
    <h2 class="text-lg font-bold text-green-700">
      آخر 5 دفوعات
    </h2>
  </div>

  <table class="w-full text-sm text-center">
    <thead class="bg-gray-50 text-gray-600">
      <tr>
        <th class="px-4 py-4">المشروع</th>
        <th class="px-4 py-4">النوع</th>
        <th class="px-4 py-4">رقم</th>
        <th class="px-4 py-4">العمارة</th>
        <th class="px-4 py-4">الشطر</th>
        <th class="px-4 py-4">طريقة الدفع</th>
        <th class="px-4 py-4">المبلغ</th>
        <th class="px-4 py-4">التاريخ</th>
      </tr>
    </thead>

    <tbody>
      <tr
        v-for="p in lastPayments"
        :key="p.id"
        class="border-t hover:bg-gray-50 transition"
      >

        <!-- المشروع -->
        <td class="px-4 py-3 font-semibold">
          {{ p.project?.name ?? p.project ?? '-' }}
        </td>

        <!-- النوع -->
        <td class="px-4 py-3">
          <span
            class="px-3 py-1 rounded-full text-xs font-bold"
            :class="{
              'bg-yellow-100 text-yellow-700': p.context === 'land',
              'bg-blue-100 text-blue-700': p.context === 'apartment',
              'bg-purple-100 text-purple-700': p.context === 'shop',
            }"
          >
            {{
              p.context === 'land'
                ? 'قطعة'
                : p.context === 'apartment'
                ? 'شقة'
                : 'محل'
            }}
          </span>
        </td>

        <!-- الرقم -->
        <td class="px-4 py-3 font-bold">
 {{ p.number ?? '-' }}
        </td>

        <!-- العمارة -->
        <td class="px-4 py-3 font-bold">
          {{ p.building_number ?? '-' }}
        </td>

        <!-- الشطر -->
        <td class="px-4 py-3 font-bold">
          {{ p.tranche_number ?? '-' }}
        </td>

        <!-- طريقة الدفع -->
        <td class="px-4 py-3 font-bold">
          {{ p.payment_method ?? '-' }}
        </td>

        <!-- المبلغ -->
        <td class="px-4 py-3 font-bold text-green-600">
          {{ formatMoney(p.amount) }}
        </td>

        <!-- التاريخ -->
        <td class="px-4 py-3 font-bold text-gray-600">
          {{ p.date ?? p.paid_at }}
        </td>

      </tr>

      <tr v-if="!lastPayments.length">
        <td colspan="8" class="py-10 text-gray-400">
          لا توجد دفوعات
        </td>
      </tr>
    </tbody>
  </table>
</div>


  </div>
</AppLayout>
</template>
