<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive, computed, ref } from 'vue'

/* ================== Props ================== */
const props = defineProps({
  customer: Object,
  units: Array,
  projects: Array,
  filters: Object,
})

/* ================== Filters (بدون لمس) ================== */
const filtersForm = reactive({
  project_id: props.filters?.project_id ?? '',
  context: props.filters?.context ?? '',
  unit_number: props.filters?.unit_number ?? '',
  building_number: props.filters?.building_number ?? '',
  tranche_number: props.filters?.tranche_number ?? '',
})

function applyFilters() {
  router.get(`/customers/${props.customer.id}`, filtersForm, {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  router.get(`/customers/${props.customer.id}`)
}

function printResults() {
  const query = new URLSearchParams(filtersForm).toString()
  window.open(`/customers/${props.customer.id}/print?${query}`, '_blank')
}


function money(v) {
  return formatMoney(v)
}

function paid(u) {
  return u.total_paid ?? 0
}

function remaining(u) {
  return (u.total_price ?? 0) - (u.total_paid ?? 0)
}

/* ================== تقسيم الوحدات ================== */
const apartments = computed(() =>
  props.units.filter(u => u.context === 'apartment')
)
const shops = computed(() =>
  props.units.filter(u => u.context === 'shop')
)
const lands = computed(() =>
  props.units.filter(u => u.context === 'land')
)

/* ================== Helpers ================== */
function formatMoney(v) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(v ?? 0)
}

/* ================== Tabs ================== */
const activeTab = ref('apartment')
</script>

<template>
<AppLayout title="ملف الزبون">
<div class="px-8 py-8 space-y-10">

        <!-- ===== Header ===== -->
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-green-700">ملـــف الزبـــون</h1>
      </div>

      <!-- ===== Customer Info ===== -->
      <div class="bg-white rounded-2xl shadow p-6 mb-10">
        <div class="grid grid-cols-3 md:grid-cols-3 gap-6 text-sm">
          <div>
            <div class="text-gray-500">الاسم</div>
            <div class="font-semibold">{{ customer.name }}</div>
          </div>

          <div>
            <div class="text-gray-500">رقم البطاقة</div>
            <div class="font-semibold">{{ customer.national_id }}</div>
          </div>

          <div>
            <div class="text-gray-500">الهاتف</div>
            <div class="font-semibold">{{ customer.phone || '-' }}</div>
          </div>
        </div>
      </div>
      <!-- ===== Filters ===== -->
      <div class="bg-white rounded-2xl shadow p-5 mb-12">
            <div  
    class="
      grid grid-cols-2 gap-x-4 gap-y-5
      md:grid-cols-3
      lg:grid-cols-6
    "
  >

          <div class="min-w-[200px] flex-1">
            <label class="text-xs text-gray-500">المشروع</label>
            <select v-model="filtersForm.project_id" class="w-full border rounded-xl px-3 py-2">
              <option value="">كل المشاريع</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
          </div>

          <div class="min-w-[140px]">
            <label class="text-xs text-gray-500">النوع</label>
            <select v-model="filtersForm.context" class="w-full border rounded-xl px-3 py-2">
              <option value="">الكل</option>
              <option value="apartment">شقة</option>
              <option value="shop">محل</option>
              <option value="land">قطعة</option>
            </select>
          </div>

          <div class="min-w-[120px]">
            <label class="text-xs text-gray-500">رقم</label>
            <input v-model="filtersForm.unit_number" class="w-full border rounded-xl px-3 py-2" />
          </div>

          <div class="min-w-[120px]">
            <label class="text-xs text-gray-500">العمارة</label>
            <input v-model="filtersForm.building_number" class="w-full border rounded-xl px-3 py-2" />
          </div>

          <div class="min-w-[120px]">
            <label class="text-xs text-gray-500">الشطر</label>
            <input v-model="filtersForm.tranche_number" class="w-full border rounded-xl px-3 py-2" />
          </div>

<div class="flex items-center gap-3">

  <!-- بحث -->
  <button
    @click="applyFilters"
    title="بحث"
    class="group h-10 w-10 flex items-center justify-center
           bg-green-600 text-white rounded-xl
           hover:bg-green-700 active:scale-95
           transition shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:scale-110 transition"
         fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
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
           active:scale-95 transition shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:rotate-180 transition"
         fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0 0 14-7M19 5a9 9 0 0 0-14 7" />
    </svg>
  </button>

  <!-- طباعة -->
  <button
    @click="printResults"
    title="طباعة"
    class="group h-10 w-10 flex items-center justify-center
           bg-yellow-50 text-yellow-600
           border border-yellow-200 rounded-xl
           hover:bg-yellow-100
           active:scale-95 transition shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:scale-110 transition"
         fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" />
    </svg>
  </button>

</div>


        </div>
      </div>

<!-- ================= TABS ================= -->
<div class="flex gap-8 border-b font-semibold">
  <button @click="activeTab='apartment'"
    :class="activeTab==='apartment' ? 'text-green-700 border-b-2 border-green-700 pb-2' : 'text-gray-400 pb-2'">
    الشقق ({{ apartments.length }})
  </button>

  <button @click="activeTab='shop'"
    :class="activeTab==='shop' ? 'text-green-700 border-b-2 border-green-700 pb-2' : 'text-gray-400 pb-2'">
    المحلات ({{ shops.length }})
  </button>

  <button @click="activeTab='land'"
    :class="activeTab==='land' ? 'text-green-700 border-b-2 border-green-700 pb-2' : 'text-gray-400 pb-2'">
    القطع ({{ lands.length }})
  </button>
</div>

<!-- ================= TABLES ================= -->
<section v-if="activeTab==='apartment' && apartments.length">
  <div class="overflow-x-auto">
<table class="w-full bg-white rounded-2xl shadow text-sm">
<thead class="bg-gray-50">
<tr>
<th class="px-4 py-3">المشروع</th>
<th class="px-4 py-3">رقم</th>
<th class="px-4 py-3">العمارة</th>
<th class="px-4 py-3">الشطر</th>
<th class="px-4 py-3 text-right">الثمن</th>
<th class="px-4 py-3 text-right">المدفوع</th>
<th class="px-4 py-3 text-right">المتبقي</th>
</tr>
</thead>
<tbody>
<tr v-for="u in apartments" :key="u.id" class="border-t hover:bg-gray-50">
<td class="px-4 py-3 font-bold">{{ u.project_name }}</td>
<td class="px-4 py-3">{{ u.number }}</td>
<td class="px-4 py-3">{{ u.building_number }}</td>
<td class="px-4 py-3">{{ u.tranche_number ?? '-' }}</td>
<td class="px-4 py-3 text-right font-bold">{{ formatMoney(u.total_price) }}</td>
<td class="px-4 py-3 text-right text-green-600 font-bold">{{ formatMoney(u.total_paid ?? 0) }}</td>
<td class="px-4 py-3 text-right text-red-600 font-bold">
{{ formatMoney((u.total_price ?? 0) - (u.total_paid ?? 0)) }}
</td>
</tr>
</tbody>
</table>
  </div>

</section>
<section v-if="activeTab==='shop' && shops.length">
        <div class="bg-white rounded-2xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3">المشروع</th>
                <th class="px-4 py-3">العمارة</th>
                <th class="px-4 py-3">رقم</th>
                <th class="px-4 py-3">الثمن</th>
                <th class="px-4 py-3">المدفوع</th>
                <th class="px-4 py-3">المتبقي</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in shops" :key="u.id" class="border-t">
                <td class="px-4 py-3">{{ u.project_name }}</td>
                <td class="px-4 py-3">{{ u.building_number }}</td>
                <td class="px-4 py-3">{{ u.number }}</td>
                <td class="px-4 py-3">{{ money(u.total_price) }}</td>
                <td class="px-4 py-3 text-green-600">{{ money(paid(u)) }}</td>
                <td class="px-4 py-3 text-red-600 font-semibold">{{ money(remaining(u)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
</section>
<section v-if="activeTab==='land' && lands.length">
        <div class="bg-white rounded-2xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3">المشروع</th>
                <th class="px-4 py-3">رقم القطعة</th>
                <th class="px-4 py-3">الطريق / الواجهة</th>
                <th class="px-4 py-3">الثمن</th>
                <th class="px-4 py-3">المدفوع</th>
                <th class="px-4 py-3">المتبقي</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in lands" :key="u.id" class="border-t">
                <td class="px-4 py-3">{{ u.project_name }}</td>
                <td class="px-4 py-3 font-semibold">{{ u.land_number }}</td>
                <td class="px-4 py-3">
                  <span class="px-3 py-1 bg-gray-100 rounded-full">
                    {{ u.road_view }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ money(u.total_price) }}</td>
                <td class="px-4 py-3 text-green-600">{{ money(paid(u)) }}</td>
                <td class="px-4 py-3 text-red-600 font-semibold">{{ money(remaining(u)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
</section>

</div>
</AppLayout>
</template>
