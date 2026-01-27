<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

/* ================== Props ================== */
const props = defineProps({
  land: Object,
  project: Object,
  summary: Object,
  payments: Array,
  ownership_history: {
    type: Array,
    default: () => [],
  },
})

/* ================== Helpers ================== */
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

/* ================== Title ================== */
const title = computed(() => {
  return `بيان القطعة رقم ${props.land.land_number} - ${props.project.name}`
})

/* ================== Actions ================== */

function openPdf() {
    const id = props.land.id
    const url = `/lands/${id}/invoice/pdf`

    // فتح PDF في نافذة جديدة فقط
    window.open(url, '_blank')
}


/* ================= BACK ================= */
function goBack() {
    router.visit(`/projects/${props.project.id}/lands?focus-land=${props.land.id}`, {
        preserveScroll: true
    });
}

/* ================= DELETE ================= */

const showDeleteModal = ref(false)
const deleting = ref(false)

function confirmDelete() {
  showDeleteModal.value = true
}

function remove() {
  deleting.value = true

  router.delete(`/lands/${props.land.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
    },
  })
}

</script>

<template>
  <AppLayout :title="title">
    <div class="p-10">

      <!-- ================== العنوان + رجوع ================== -->
      <div class="flex items-center justify-between mb-8 border-b pb-3 px-6 print:hidden">
        <h1 class="text-2xl font-bold text-gray-800">
          {{ title }}
        </h1>

        <button
          @click="goBack"
          class="flex items-center gap-2 px-6 py-2 border-2 border-green-600 text-green-700
                 rounded-xl hover:bg-green-50 transition font-bold shadow-sm"
        >
          
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7l-5 5m0 0l5 5m-5-5h18" />
          </svg>
        </button>
      </div>

      <!-- ================== التخطيط الرئيسي ================== -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- =====================================================
             العمود الرئيسي (⅔)
        ====================================================== -->
        <div class="lg:col-span-2 flex flex-col gap-6">
<!-- ===== بطاقة معلومات القطعة + الصورة ===== -->
<div class="bg-white rounded-2xl shadow p-6 h-full flex flex-col">

  <h3 class="text-lg font-bold mb-4 border-b pb-2">
    معلومات القطعة
  </h3>

  <!-- GRID 2/3 + 1/3 -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1">

    <!-- ================== المعلومات (⅔) ================== -->
    <div class="lg:col-span-2 flex flex-col">

      <div class="grid grid-cols-2 gap-4 text-sm">

        <div>
          <p class="text-gray-500 font-bold">رقم القطعة</p>
          <p class="font-semibold">{{ land.land_number }}</p>
        </div>

        <div>
          <p class="text-gray-500 font-bold">المساحة</p>
          <p class="font-semibold">{{ land.area }} م²</p>
        </div>

        <div>
          <p class="text-gray-500 font-bold">الواجهة</p>
          <p class="font-semibold">{{ land.view_type }}</p>
        </div>

        <div>
          <p class="text-gray-500 font-bold">عرض الطريق</p>
          <p class="font-semibold">{{ land.road_type }} م</p>
        </div>

        <div>
          <p class="text-gray-500 font-bold">صاحب القطعة</p>
          <p class="font-semibold">
            {{ land.customer_name ?? '—' }}
          </p>
        </div>

        <!-- ===== التخفيض (إن وجد) ===== -->
        <div v-if="land.discount && land.discount > 0">
          <p class="text-gray-500 font-bold">قيمة التخفيض</p>
          <p class="font-semibold text-red-600">
            - {{ formatMoney(land.discount) }}
          </p>
        </div>

      </div>

      <!-- ===== الثمن الإجمالي (بعد التخفيض – من DB) ===== -->
      <div class="mt-auto pt-4 border-t text-center">
        <p class="text-gray-500 text-sm font-bold">الثمن الإجمالي</p>
        <p class="text-xl font-bold text-green-600">
          {{ formatMoney(land.total_price) }}
        </p>
      </div>

    </div>

    <!-- ================== الصورة (⅓) ================== -->
    <div class="flex flex-col">

      <div
        class="border-2 border-dashed rounded-xl
               flex-1 flex items-center justify-center
               overflow-hidden bg-gray-50"
      >
        <template v-if="land.image">
          <img
            :src="`/storage/${land.image}`"
            class="w-full h-full object-cover"
          />
        </template>

        <template v-else>
          <div class="text-gray-400 text-sm flex flex-col items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-10 h-10 opacity-40"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5h18M3 19h18M5 5v14M19 5v14"/>
            </svg>
            لا توجد صورة
          </div>
        </template>
      </div>

    </div>

  </div>
</div>


<!-- ===== الدفوعات + التنازلات ===== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
          <!-- ===== بطاقة الدفوعات ===== -->
          <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">
              الدفوعات
            </h3>

            <table class="w-full text-sm">
              <thead class="text-gray-500">
                <tr>
                  <th class="text-right py-2">التاريخ</th>
                  <th class="text-center">المبلغ</th>
                  <th class="text-center">النسبة</th>
                  <th class="text-left">الطريقة</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="p in payments"
                  :key="p.id"
                  class="border-t"
                >
                  <td class="py-2">
                    {{ p.paid_at?.split('T')[0]?.split(' ')[0] }}
                  </td>

                  <td class="text-center text-green-600 font-semibold">
                    {{ formatMoney(p.amount) }}
                  </td>

                  <td class="text-center">
                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                      {{ p.percentage }} %
                    </span>
                  </td>

                  <td class="text-left">{{ p.method }}</td>
                </tr>

                <tr v-if="payments.length === 0">
                  <td colspan="4" class="text-center py-6 text-gray-400">
                    لا توجد دفوعات
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
                    <!-- ===== بطاقة التنازلات ===== -->
          <div class="bg-white rounded-2xl shadow p-6 h-full">
  <h3 class="text-lg font-bold mb-4 border-b pb-2">
   التنازلات
  </h3>

  <table v-if="ownership_history.length" class="w-full text-sm text-right">
    <thead class="text-gray-500">
      <tr>
        <th>المالك</th>
        <th>رقم التنازل</th>
        <th>التاريخ</th>
      </tr>
    </thead>

    <tbody>
      <tr
        v-for="(row, index) in ownership_history"
        :key="index"
        class="border-t"
      >
        <!-- الاسم -->
        <td class="py-3 font-semibold">
          {{ row.name }}
          <span
            v-if="index === 0"
            class="ml-2 px-2 py-0.5 text-xs rounded-full
                   bg-green-600 text-white"
          >
            المالك 
          </span>
        </td>

        <!-- رقم التنازل -->
        <td class="py-3 font-bold text-center">
          {{ row.transfer_number }}
        </td>

        <!-- التاريخ -->
        <td class="py-3 text-gray-600">
          {{ row.date }}
        </td>
      </tr>
    </tbody>
  </table>

  <p v-else class="text-center text-gray-400 py-10">
    لا توجد تنازلات مسجلة
  </p>
         </div>
</div>
        </div>

        <!-- =====================================================
             العمود الجانبي (⅓) : الملخص المالي + الأزرار
        ====================================================== -->
        <div class="flex flex-col gap-6">

          <!-- ===== الملخص المالي ===== -->
<div class="bg-white rounded-2xl shadow p-5 flex flex-col gap-4 h-full">

  <!-- العنوان -->
  <h3 class="text-base font-bold text-gray-800 border-b pb-2">
    الملخص المالي
  </h3>

  <!-- KPI GRID -->
  <div class="grid grid-cols-2 gap-3">

    <!-- المدفوع -->
    <div class="border rounded-xl p-4 text-center">
      <p class="text-xs text-gray-500 font-semibold mb-1">
        المدفوع
      </p>
      <p class="text-lg font-bold text-green-600">
        {{ formatMoney(summary.paid) }}
      </p>
    </div>

    <!-- المتبقي -->
    <div class="border rounded-xl p-4 text-center">
      <p class="text-xs text-gray-500 font-semibold mb-1">
        المتبقي
      </p>
      <p class="text-lg font-bold text-red-600">
        {{ formatMoney(summary.remaining) }}
      </p>
    </div>

  </div>

  <!-- نسبة الأداء -->
  <div class="border rounded-xl p-4">
    <div class="flex justify-between items-center mb-2">
      <p class="text-xs text-gray-500 font-semibold">
        نسبة الأداء
      </p>
      <span class="text-sm font-bold text-gray-700">
        {{ summary.total > 0
          ? ((summary.paid / summary.total) * 100).toFixed(1)
          : 0
        }} %
      </span>
    </div>

    <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
      <div
        class="h-full bg-green-600 transition-all duration-500"
        :style="{
          width: `${summary.total > 0
            ? (summary.paid / summary.total) * 100
            : 0
          }%`
        }"
      ></div>
    </div>
  </div>

</div>


          <!-- ===== الأزرار (تحت الملخص) ===== -->
    <!-- ================== الأزرار ================== -->
    <div class="flex flex-wrap justify-center gap-8 mt-10 print:hidden">

      <!-- تعديل -->
      <a
        :href="`/lands/${land.id}/edit`"
        class="flex flex-col items-center gap-2 group"
      >
        <div
            class="w-14 h-14 flex items-center justify-center
                   rounded-full border border-black-500
                   text-blue-600 
                   shadow-sm transition group-hover:scale-105"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16.862 4.487l1.425 1.425c.547.547.547 1.433 0 1.98l-8.91 8.91-3.038.674.674-3.038 8.91-8.91c.547-.547 1.433-.547 1.98 0z"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700">
            تعديل
        </span>
      </a>

      <!-- حذف -->
      <button
        @click="confirmDelete"
        class="flex flex-col items-center gap-2 group"
      >
        <div
            class="w-14 h-14 flex items-center justify-center
                   rounded-full border border-black-500
                   text-red-600 
                   shadow-sm transition group-hover:scale-105"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700">
            حذف
        </span>
      </button>

      <!-- PDF -->
      <button
        @click="openPdf"
        class="flex flex-col items-center gap-2 group"
      >
        <div
            class="w-14 h-14 flex items-center justify-center
                   rounded-full border border-black-500
                   text-yellow-600 
                   shadow-sm transition group-hover:scale-105"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM14 2v6h6"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 15h6m-6 4h4"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700">
            PDF
        </span>
      </button>

      <!-- إضافة دفعة -->
      <a
        :href="`/lands/${land.id}/payments/create`"
        class="flex flex-col items-center gap-2 group"
      >
        <div
            class="w-14 h-14 flex items-center justify-center
                   rounded-full border border-black-500
                   text-green-600 
                   shadow-sm transition group-hover:scale-105"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700">
            إضافة دفعة
        </span>
      </a>
      <!-- تسجيل تنازل -->
    <a
    :href="`/transfers/create/land/${land.id}`"
    class="flex flex-col items-center gap-2 group"
>
    <div
        class="w-14 h-14 flex items-center justify-center
               rounded-full border border-black-500
               text-purple-600
               shadow-sm transition group-hover:scale-105"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-6 h-6"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 8l4 4m0 0l-4 4m4-4H7"/>
        </svg>
    </div>
    <span class="text-sm font-semibold text-gray-700">
        تسجيل تنازل
    </span>
    </a>

    </div>

        </div>

            <!-- Delete land Modal -->
<div
  v-if="showDeleteModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف القطعة الأرضية
    </h2>

    <!-- الجملة -->
    <p class="text-gray-700 text-lg leading-relaxed mb-6">
      هل تريد حذف
      <span class="font-bold text-gray-900">
        القطعة رقم {{ land.land_number }}
      </span>
      التابعة لمشروع
      <span class="font-bold text-gray-900">
        {{ project.name }}
      </span>
      ؟
    </p>

    <!-- تحذير -->
    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      هذا الإجراء نهائي ولا يمكن التراجع عنه
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
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف القطعة' }}
      </button>

    </div>
</div>
  </div>
      </div>
    </div>
  </AppLayout>
</template>

