<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { reactive, computed, ref } from 'vue'

/* ===================== PROPS ===================== */
const props = defineProps({
  commissions: Object,
  projects: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

/* ===================== FILTERS ===================== */
const filtersForm = reactive({
  project_id: props.filters.project_id ?? '',
  context: props.filters.context ?? '',
  unit_number: props.filters.unit_number ?? '',
})

function applyFilters() {
  router.get('/commissions', filtersForm, {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  router.get('/commissions', {}, { replace: true })
}

/* ===================== ACTIONS ===================== */
function edit(id) {
  router.get(`/commissions/${id}/edit`)
}

function remove(id) {
  if (!confirm('هل أنت متأكد من حذف السمسرة؟')) return
  router.delete(`/commissions/${id}`)
}

/* ===================== HELPERS ===================== */
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

function contextLabel(context) {
  if (context === 'land') return 'قطعة'
  if (context === 'apartment') return 'شقة'
  return 'محل'
}

function projectName(c) {
  if (c.context === 'land') {
    return c.land?.project?.name ?? '-'
  }

  if (c.context === 'apartment') {
    return c.apartment?.building?.project?.name ?? '-'
  }

  if (c.context === 'shop') {
    return c.shop?.building?.project?.name ?? '-'
  }

  return '-'
}

function unitNumber(c) {
  if (c.context === 'land') {
    return c.land?.land_number ?? '-'
  }

  if (c.context === 'apartment') {
    return c.apartment?.number ?? '-'
  }

  if (c.context === 'shop') {
    return c.shop?.number ?? '-'
  }

  return '-'
}


</script>

<template>
<AppLayout title="السمسرة">
  <div class="p-8">

    <!-- ===== العنوان ===== -->
    <div class="mb-6 text-right flex justify-between items-center">
      <h1 class="text-2xl font-bold text-green-700"> السمسرة</h1>
    </div>

    <!-- ===== الفلاتر ===== -->
    <div class="bg-white rounded-2xl shadow p-5 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- المشروع -->
        <div>
          <label class="text-xs text-gray-500 mb-1 block">المشروع</label>
          <select v-model="filtersForm.project_id"
                  class="w-full border rounded-xl px-3 py-2">
            <option value="">كل المشاريع</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">
              {{ p.name }}
            </option>
          </select>
        </div>

        <!-- النوع -->
        <div>
          <label class="text-xs text-gray-500 mb-1 block">النوع</label>
          <select v-model="filtersForm.context"
                  class="w-full border rounded-xl px-3 py-2">
            <option value="">الكل</option>
            <option value="land">قطعة</option>
            <option value="apartment">شقة</option>
            <option value="shop">محل</option>
          </select>
        </div>

        <!-- رقم -->
        <div>
          <label class="text-xs text-gray-500 mb-1 block">رقم الوحدة</label>
          <input
            v-model="filtersForm.unit_number"
            type="text"
            class="w-full border rounded-xl px-3 py-2"
            placeholder="رقم"
          />
        </div>

        <!-- الأزرار -->
        <div class="flex items-end gap-3">
  <!-- بحث -->
  <button
    @click="applyFilters"
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
    @click="resetFilters"
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

    <!-- ===== الجدول ===== -->
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
      <table class="w-full text-sm text-center">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-4 py-4">المشروع</th>
            <th class="px-4 py-4">النوع</th>
            <th class="px-4 py-4">الرقم</th>
            <th class="px-4 py-4">المبلغ</th>
            <th class="px-4 py-4">السمسار</th>
            <th class="px-4 py-4">التاريخ</th>
            <th class="px-4 py-4">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="c in commissions.data"
            :key="c.id"
            class="border-t hover:bg-gray-50 transition"
          >
           <td class="px-4 py-3 font-semibold">
  {{ projectName(c) }}
</td>


            <td class="px-4 py-3">
              <span
                class="px-3 py-1 rounded-full text-xs font-bold"
                :class="{
                  'bg-yellow-100 text-yellow-700': c.context === 'land',
                  'bg-blue-100 text-blue-700': c.context === 'apartment',
                  'bg-purple-100 text-purple-700': c.context === 'shop',
                }"
              >
                {{ contextLabel(c.context) }}
              </span>
               </td>

             <td class="px-4 py-3 font-bold">
             {{ unitNumber(c) }}
             </td>


            <td class="px-4 py-3 font-bold text-green-600">
              {{ formatMoney(c.amount) }}
            </td>

            <td class="px-4 py-3 font-bold">
              {{ c.broker_name ?? '-' }}
            </td>

<td class="px-4 py-3 font-bold">
  {{ c.commission_date }}
</td>


            <td class="px-4 py-3">
  <div class="flex justify-center gap-2">

    <!-- تعديل -->
    <button
      @click="edit(c.id)"
      class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition"
      title="تعديل"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor"
           stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.862 3.487a2.25 2.25 0 013.182 3.182L8.25 18.463
                 3.75 20.25l1.787-4.5L16.862 3.487z" />
      </svg>
    </button>

    <!-- حذف -->
    <button
      @click="remove(c.id)"
      class="p-2 text-red-600 hover:bg-red-50 rounded-full transition"
      title="حذف"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862
                 a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6
                 M9 7h6m2 0H7"/>
      </svg>
    </button>

    <!-- طباعة -->
    <a
      :href="`/commissions/${c.id}/print`"
      target="_blank"
      class="p-2 text-green-600 hover:bg-green-50 rounded-full transition"
      title="طباعة"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5
                 a2 2 0 012-2h16a2 2 0 012 2v5
                 a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
      </svg>
    </a>

  </div>
</td>

          </tr>

          <tr v-if="commissions.data.length === 0">
            <td colspan="7" class="py-10 text-gray-400">
              لا توجد سمسرات مسجلة
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ===== Pagination ===== -->
    <div v-if="commissions.links.length > 3" class="flex justify-center mt-10">
      <nav class="flex gap-2 bg-white px-4 py-3 rounded-2xl shadow-md">
        <button
          v-for="(link, i) in commissions.links"
          :key="i"
          v-html="link.label"
          @click="link.url && router.visit(link.url)"
          :disabled="!link.url"
          class="min-w-[40px] h-10 px-3 rounded-xl text-sm"
          :class="{
            'bg-green-600 text-white': link.active,
            'text-gray-600 hover:bg-gray-100': !link.active && link.url,
            'text-gray-300 cursor-not-allowed': !link.url,
          }"
        />
      </nav>
    </div>

  </div>
</AppLayout>
</template>
