<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, ref, computed } from 'vue'

/* ===================== PROPS ===================== */
const props = defineProps({
  transfers: {
    type: Array,
    default: () => [],
  },
  projects: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

/* ===================== DELETE ===================== */
const showDeleteModal = ref(false)
const deleting = ref(false)
const transferToDelete = ref(null)

function confirmDelete(transfer) {
  transferToDelete.value = transfer
  showDeleteModal.value = true
}

function remove() {
  if (!transferToDelete.value?.id) return

  deleting.value = true
  router.delete(`/transfers/${transferToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
      transferToDelete.value = null
    },
  })
}

/* ===================== FILTER STATE ===================== */
const filtersForm = reactive({
  project_id: props.filters.project_id ?? '',
  context: props.filters.context ?? '',
  unit_number: props.filters.unit_number ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})

const unitPlaceholder = computed(() => {
  if (filtersForm.context === 'apartment') return 'رقم الشقة'
  if (filtersForm.context === 'shop') return 'رقم المحل'
  if (filtersForm.context === 'land') return 'رقم القطعة'
  return 'رقم الوحدة'
})

function applyFilters() {
  router.get('/transfers', filtersForm, {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  router.get('/transfers', {}, { replace: true })
}

/* ===================== FORMAT ===================== */

function restoreOwnership(transfer) {
  if (!transfer?.id) return

  router.post(`/transfers/${transfer.id}/restore-ownership`, {}, {
    preserveScroll: true,
  })
}



function formatDate(date) {
  return date ?? '-'
}
</script>

<template>
<AppLayout title="التنازلات">
  <div class="p-8">

    <!-- العنوان -->
    <div class="mb-6 text-right">
      <h1 class="text-2xl font-bold text-green-700">التنازلات</h1>
    </div>

    <!-- ================== الفلاتر ================== -->
    <div class="bg-white rounded-2xl shadow p-5 mb-8">
      <div class="flex flex-wrap items-end gap-4">

        <!-- المشروع -->
        <div class="min-w-[180px] flex-1">
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
        <div class="min-w-[130px]">
          <label class="text-xs text-gray-500 mb-1 block">النوع</label>
          <select v-model="filtersForm.context"
                  class="w-full border rounded-xl px-3 py-2">
            <option value="">الكل</option>
            <option value="apartment">شقة</option>
            <option value="shop">محل</option>
            <option value="land">قطعة</option>
          </select>
        </div>

        <!-- رقم الوحدة -->
        <div class="min-w-[150px]">
          <label class="text-xs text-gray-500 mb-1 block">رقم</label>
          <input
            v-model="filtersForm.unit_number"
            type="text"
            class="w-full border rounded-xl px-3 py-2"
            :placeholder="unitPlaceholder"
          />
        </div>

        <!-- من / إلى -->
        <div class="min-w-[150px]">
          <label class="text-xs text-gray-500 mb-1 block">من تاريخ</label>
          <input v-model="filtersForm.date_from" type="date"
                 class="w-full border rounded-xl px-3 py-2" />
        </div>

        <div class="min-w-[150px]">
          <label class="text-xs text-gray-500 mb-1 block">إلى تاريخ</label>
          <input v-model="filtersForm.date_to" type="date"
                 class="w-full border rounded-xl px-3 py-2" />
        </div>

        <!-- أزرار -->
        <div class="flex items-center gap-3">
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

    <!-- ================== الجدول ================== -->
<div class="bg-white rounded-2xl shadow overflow-x-auto">
  <table class="w-full text-sm text-center">
    <thead class="bg-gray-50 text-gray-600">
      <tr>
        <th class="px-4 py-4">المشروع</th>
        <th class="px-4 py-4">النوع</th>
        <th class="px-4 py-4">الوحدة</th>
        <th class="px-4 py-4">المتنازل</th>
        <th class="px-4 py-4">المستفيد </th>
        <th class="px-4 py-4">رقم التنازل</th>
        <th class="px-4 py-4">التاريخ</th>
        <th class="px-4 py-4">إجراءات</th>
      </tr>
    </thead>

    <tbody>
      <tr
        v-for="transfer in transfers.data"
        :key="transfer.id"
        class="border-t hover:bg-gray-50 transition"
      >

        <!-- المشروع -->
        <td class="px-4 py-3 font-semibold">
          {{ transfer.project?.name ?? '-' }}
        </td>

        <!-- النوع -->
        <td class="px-4 py-3">
          <span
            class="px-3 py-1 rounded-full text-xs font-bold"
            :class="{
              'bg-blue-100 text-blue-700': transfer.context === 'apartment',
              'bg-purple-100 text-purple-700': transfer.context === 'shop',
              'bg-yellow-100 text-yellow-700': transfer.context === 'land',
            }"
          >
            {{
              transfer.context === 'apartment'
                ? 'شقة'
                : transfer.context === 'shop'
                ? 'محل'
                : 'قطعة'
            }}
          </span>
        </td>

        <!-- الوحدة -->
        <td class="px-4 py-3 font-bold">
          {{ transfer.unit_label ?? '-' }}
        </td>

        <!-- المتنازل -->
        <td class="px-4 py-3 font-bold">
          {{ transfer.from_customer?.name ?? '-' }}
        </td>

        <!-- المستفيد -->
        <td class="px-4 py-3 font-bold">
          {{ transfer.to_customer?.name ?? '-' }}
        </td>

        <!-- رقم التنازل -->
        <td class="px-4 py-3 font-bold">
          {{ transfer.transfer_number }}
        </td>

        <!-- التاريخ -->
        <td class="px-4 py-3 text-gray-600 font-bold">
          {{ formatDate(transfer.transfer_date) }}
        </td>

        <!-- الإجراءات -->
        <td class="text-center">
          <div class="flex justify-center gap-3">
            
            <!-- تعديل  -->
<Link
  :href="`/transfers/${transfer.id}/edit`"
  class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition"
  title="تعديل التنازل"
>
  <svg xmlns="http://www.w3.org/2000/svg"
       class="w-5 h-5"
       fill="none"
       viewBox="0 0 24 24"
       stroke="currentColor"
       stroke-width="2">
    <path stroke-linecap="round"
          stroke-linejoin="round"
          d="M16.862 3.487a2.25 2.25 0
             013.182 3.182L8.25 18.463
             3.75 20.25l1.787-4.5
             11.325-12.263z"/>
  </svg>
</Link>


            <!-- حذف -->
    <button
      @click="confirmDelete(transfer)"
      class="p-2 text-red-600 hover:bg-red-50 rounded-full transition"
      title="حذف"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor"
           stroke-width="2">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              d="M19 7l-.867 12.142A2 2 0
                 0116.138 21H7.862a2 2 0
                 01-1.995-1.858L5 7m5
                 4v6m4-6v6M9 7h6m2 0H7"/>
      </svg>
    </button>


    <!-- طباعة -->
    <a
      :href="`/transfers/${transfer.id}/print`"
      target="_blank"
      class="p-2 text-green-600 hover:bg-green-50 rounded-full transition"
      title="طباعة"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor"
           stroke-width="2">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              d="M6 9V2h12v7M6 18H4a2
                 2 0 01-2-2v-5a2 2 0
                 012-2h16a2 2 0 012 2v5
                 a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
      </svg>
    </a>


          </div>
        </td>
      </tr>

      <tr v-if="transfers.data.length === 0">
        <td colspan="8" class="py-10 text-gray-400">
          لا توجد تنازلات مسجلة
        </td>
      </tr>
    </tbody>
  </table>
</div>
<!-- ================= Pagination ================= -->
<div
  v-if="transfers.links.length > 3"
  class="flex justify-center mt-10"
>
  <nav
    class="flex items-center gap-2
           bg-white px-4 py-3
           rounded-2xl shadow-md"
  >
    <button
      v-for="(link, index) in transfers.links"
      :key="index"
      v-html="link.label"
      @click="link.url && router.visit(link.url)"
      :disabled="!link.url"
      class="min-w-[40px] h-10 px-3
             flex items-center justify-center
             rounded-xl text-sm font-medium
             transition-all duration-200"
      :class="{
        // الصفحة الحالية
        'bg-green-600 text-white shadow-md scale-105':
          link.active,

        // روابط قابلة للنقر
        'text-gray-600 hover:bg-gray-100':
          !link.active && link.url,

        // روابط معطلة (Previous / Next)
        'text-gray-300 cursor-not-allowed':
          !link.url
      }"
    />
  </nav>
</div>



  </div>

  <!-- ================= مودال الحذف ================= -->
  <div
    v-if="showDeleteModal && transferToDelete"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">
      <h2 class="text-xl font-bold text-red-600 mb-4">
        تأكيد حذف التنازل
      </h2>

      <p class="mb-6">
        هل تريد حذف التنازل رقم
        <strong>{{ transferToDelete.transfer_number }}</strong>؟
      </p>

      <div class="flex justify-center gap-4">
        <button @click="showDeleteModal = false"
                class="px-6 py-2 border rounded-xl">
          إلغاء
        </button>

        <button @click="remove"
                :disabled="deleting"
                class="px-6 py-2 bg-red-600 text-white rounded-xl">
          {{ deleting ? 'جاري الحذف...' : 'نعم، احذف' }}
        </button>
      </div>
    </div>
  </div>

</AppLayout>
</template>