<script setup >
import AppLayout from '@/layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import { ref } from 'vue'


/* ===================== PROPS ===================== */
const props = defineProps({
  payments: Array,
  projects: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

function paymentMethodLabel(method) {
  switch (method) {
    case 'cash':
      return 'نقدًا'
    case 'check':
      return 'شيك'
    case 'transfer':
      return 'تحويل بنكي'
    case 'bill':
      return 'كمبيالة'
    default:
      return method ?? '-'
  }
}

/* ===================== DELETE ===================== */


const showDeleteModal = ref(false)
const deleting = ref(false)
const paymentToDelete = ref(null)

// فتح المودال
function confirmDelete(payment) {
  paymentToDelete.value = payment
  showDeleteModal.value = true
}

// تنفيذ الحذف
function remove() {
  if (!paymentToDelete.value) return

  deleting.value = true

  router.delete(`/payments/${paymentToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
      paymentToDelete.value = null
    },
  })
}

/* ===================== FILTER STATE ===================== */
const filtersForm = reactive({
  project_id: props.filters.project_id ?? '',
  context: props.filters.context ?? '',
  unit_number: props.filters.unit_number ?? '',
  building_number: props.filters.building_number ?? '',
  tranche_number: props.filters.tranche_number ?? '',
})

const unitPlaceholder = computed(() => {
  if (filtersForm.context === 'apartment') return 'رقم الشقة'
  if (filtersForm.context === 'shop') return 'رقم المحل'
  if (filtersForm.context === 'land') return 'رقم القطعة'
  return 'رقم الوحدة'
})

function applyFilters() {
  router.get('/payments', filtersForm, {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  router.get('/payments', {}, { replace: true })
}

/* ===================== ACTIONS ===================== */
function edit(id) {
  router.get(`/payments/${id}/edit`)
}

function printResults() {
  const query = new URLSearchParams(filtersForm).toString()
  window.open(`/payments/print?${query}`, '_blank')
}

/* ===================== FORMAT ===================== */
function formatDate(date) {
  if (!date) return '-'
  return date
}

function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}



</script>

<template>
<AppLayout title="الدفوعات">
  <div class="p-8">

    <!-- العنوان -->
    <div class="mb-6 text-right">
      <h1 class="text-2xl font-bold text-green-700">الدفوعات</h1>
    </div>


<!-- ================== الفلاتر ================== -->
<div class="bg-white rounded-2xl shadow p-5 mb-8">
  <div class="flex flex-wrap items-end gap-4 w-full">

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

    <!-- رقم  -->
    <div class="min-w-[150px]">
      <label class="text-xs text-gray-500 mb-1 block">رقم </label>
      <input
        v-model="filtersForm.unit_number"
        type="text"
        class="w-full border rounded-xl px-3 py-2"
        placeholder="رقم"
      />
    </div>

    <!-- العمارة -->
    <div class="min-w-[120px]">
      <label class="text-xs text-gray-500 mb-1 block">العمارة</label>
      <input
        v-model="filtersForm.building_number"
        type="text"
        class="w-full border rounded-xl px-3 py-2"
        placeholder="عمارة"
      />
    </div>

    <!-- الشطر -->
    <div class="min-w-[120px]">
      <label class="text-xs text-gray-500 mb-1 block">الشطر</label>
      <input
        v-model="filtersForm.tranche_number"
        type="text"
        class="w-full border rounded-xl px-3 py-2"
        placeholder="شطر"
      />
    </div>

    <!-- من تاريخ -->
    <div class="min-w-[150px]">
      <label class="text-xs text-gray-500 mb-1 block">من تاريخ</label>
      <input
        v-model="filtersForm.date_from"
        type="date"
        class="w-full border rounded-xl px-3 py-2"
      />
    </div>

    <!-- إلى تاريخ -->
    <div class="min-w-[150px]">
      <label class="text-xs text-gray-500 mb-1 block">إلى تاريخ</label>
      <input
        v-model="filtersForm.date_to"
        type="date"
        class="w-full border rounded-xl px-3 py-2"
      />
    </div>

<!-- الأزرار -->
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

  <!-- طباعة -->
  <button
    @click="printResults"
    title="طباعة"
    class="group h-10 w-10 flex items-center justify-center
           bg-yellow-50 text-yellow-600
           border border-yellow-200 rounded-xl
           hover:bg-yellow-100
           active:scale-95 transition-all shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 group-hover:scale-110 transition"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
      <path stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" />
    </svg>
  </button>

</div>


  </div>
</div>



    <!-- ================== الجدول (نفس كودك) ================== -->
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
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
            <th class="px-4 py-4">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="payment in payments.data"
              :key="payment.id"
              class="border-t hover:bg-gray-50 transition">

            <!-- المشروع -->
            <td class="px-4 py-3 font-semibold">
              {{ payment.project?.name ?? '-' }}
            </td>

            <!-- النوع -->
            <td class="px-4 py-3">
              <span
                class="px-3 py-1 rounded-full text-xs font-bold"
                :class="{
                  'bg-yellow-100 text-yellow-700': payment.context === 'land',
                  'bg-blue-100 text-blue-700': payment.context === 'apartment',
                  'bg-purple-100 text-purple-700': payment.context === 'shop',
                }">
                {{
                  payment.context === 'land'
                    ? 'قطعة'
                    : payment.context === 'apartment'
                    ? 'شقة'
                    : 'محل'
                }}
              </span>
            </td>

            <!-- الرقم -->
            <td class="px-4 py-3 font-bold">
              <template v-if="payment.context === 'apartment'">
                {{ payment.apartment?.number ?? '-' }}
              </template>
              <template v-else-if="payment.context === 'shop'">
                {{ payment.shop?.number ?? '-' }}
              </template>
              <template v-else>
                {{ payment.land?.land_number ?? '-' }}
              </template>
            </td>

            <td class="px-4 py-3 font-bold">
              {{ payment.building_number ?? '-' }}
            </td>

            <td class="px-4 py-3 font-bold">
              {{ payment.tranche_number ?? '-' }}
            </td>

            <td class="px-4 py-3 font-bold">
              {{ paymentMethodLabel(payment.payment_method) }}
            </td>

            <td class="px-4 py-3 font-bold text-green-600">
              {{ formatMoney(payment.amount) }}
            </td>

            <td class="px-4 py-3 text-gray-600">
              {{ formatDate(payment.paid_at) }}
            </td>

 <!-- الإجراءات -->
<td class="text-center">
  <div class="flex justify-center gap-3">

<!-- تعديل -->
<button
  @click="edit(payment.id)"
  class="text-blue-600 hover:text-blue-800 transition"
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
@click="confirmDelete(payment)"
 class="p-2 text-red-600 hover:bg-red-50 rounded-full">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
       viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7"/>
  </svg>
</button>

<!-- طباعة -->
<a
  :href="`/payments/${payment.id}/receipt`"
  target="_blank"
  class="p-2 text-green-600 hover:bg-green-50 rounded-full"
>
  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
       viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
  </svg>
</a>



  </div>
</td>


          </tr>

          <tr v-if="payments.data.length === 0">
            <td colspan="9" class="py-10 text-gray-400">
              لا توجد دفوعات مسجلة
            </td>
          </tr>
        </tbody>
      </table>
    </div>

<!-- ================= Pagination ================= -->
<div
  v-if="payments.links.length > 3"
  class="flex justify-center mt-10"
>
  <nav
    class="flex items-center gap-2 bg-white
           px-4 py-3 rounded-2xl shadow-md"
  >
    <button
      v-for="(link, index) in payments.links"
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

        // أزرار قابلة للنقر
        'text-gray-600 hover:bg-gray-100':
          !link.active && link.url,

        // أزرار معطلة
        'text-gray-300 cursor-not-allowed':
          !link.url
      }"
    />
  </nav>
</div>


  </div>
  <!-- Delete Payment Modal -->
<div
  v-if="showDeleteModal && paymentToDelete"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف الدفعة
    </h2>

    <!-- النص -->
    <p class="text-gray-700 text-lg leading-relaxed mb-6">
      هل تريد حذف هذه الدفعة بقيمة
      <span class="font-bold text-gray-900">
        {{ paymentToDelete.amount }} درهم
      </span>
      ؟
    </p>

    <!-- تحذير -->
    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      ⚠️ هذا الإجراء نهائي ولا يمكن التراجع عنه
    </div>

    <!-- الأزرار -->
    <div class="flex justify-center gap-4">

      <button
        @click="showDeleteModal = false"
        class="px-8 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
        :disabled="deleting"
      >
        إلغاء
      </button>

      <button
        @click="remove"
        class="px-8 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 transition disabled:opacity-50"
        :disabled="deleting"
      >
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف الدفعة' }}
      </button>

    </div>
  </div>
</div>

</AppLayout>
</template>
