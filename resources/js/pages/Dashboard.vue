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
        جليـــلي إخـــوان  
      </h1>
    </div>

<!-- ================= بطاقة البحث (مطابقة customers) ================= -->
<div class="bg-white rounded-2xl shadow p-5 mb-8">
  <div     class="
      flex items-end gap-3
      flex-nowrap
      md:flex-wrap
    "
  >

    <!-- حقل البحث -->
    <div class="flex-1 min-w-0">


      <input
        v-model="form.search"
        type="text"
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
<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">

  <!-- الشركات -->
  <div class="bg-white rounded-2xl shadow p-5 flex items-center gap-4">
    <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-green-50">
      <!-- Company / Building (واضحة جدًا) -->
      <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 21h18v-2H3v2zm2-4h4V7H5v10zm5 0h4V3h-4v14zm5 0h4V11h-4v6z"/>
      </svg>
    </div>

    <div>
      <div class="text-sm text-gray-500 font-semibold">الشركات</div>
      <div class="text-2xl font-extrabold text-gray-800">
        {{ stats.companies }}
      </div>
    </div>
  </div>

  <!-- المشاريع -->
  <div class="bg-white rounded-2xl shadow p-5 flex items-center gap-4">
    <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-green-50">
<!-- Projects / Plan -->
<svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
  <path d="M3 4h18v2H3V4zm0 4h12v2H3V8zm0 4h18v2H3v-2zm0 4h12v2H3v-2z"/>
</svg>

    </div>

    <div>
      <div class="text-sm text-gray-500 font-semibold">المشاريع</div>
      <div class="text-2xl font-extrabold text-gray-800">
        {{ stats.projects }}
      </div>
    </div>
  </div>

  <!-- الزبناء -->
  <div class="bg-white rounded-2xl shadow p-5 flex items-center gap-4">
    <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-green-50">
      <!-- Users (أوضح وأكثر توازن) -->
      <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm-7 9v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1z"/>
      </svg>
    </div>

    <div>
      <div class="text-sm text-gray-500 font-semibold">الزبناء</div>
      <div class="text-2xl font-extrabold text-gray-800">
        {{ stats.customers }}
      </div>
    </div>
  </div>

  <!-- دفوعات اليوم -->
  <div class="bg-white rounded-2xl shadow p-5 flex items-center gap-4">
    <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-green-50">
      <!-- Payments / Wallet -->
      <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M2 7a3 3 0 0 1 3-3h14a1 1 0 0 1 1 1v2H5a1 1 0 0 0 0 2h16v8a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V7zm15 6h2v2h-2v-2z"/>
      </svg>
    </div>

    <div>
      <div class="text-sm text-gray-500 font-semibold">دفوعات اليوم</div>
      <div class="text-2xl font-bold text-green-600">
        {{ formatMoney(stats.payments_sum) }}
      </div>
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
