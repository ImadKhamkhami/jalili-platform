<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/* ===============================
   Props
================================ */
const props = defineProps({
    shop: {
        type: Object,
        default: null,
    },
    project: Object,
    building_number: String,
    summary: Object,
    payments: {
        type: Array,
        default: () => [],
    },
  /* ✅ الجديد */
  ownership_history: {
    type: Array,
    default: () => [],
  },
})



const deleting = ref(false)
const showDeleteModal = ref(false)
/* ===============================
   الحدف
================================ */
function confirmDelete() {
  showDeleteModal.value = true
}

function remove() {
  deleting.value = true

  router.delete(`/shops/${props.shop.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
    },
  })
}

/* 🔙 الرجوع مع focus-shop (كما كان) */
function goBack() {
  router.visit(
    `/projects/${props.project.id}/apartments`
    + `?building=${props.shop.building.name}`
    + (props.shop.tranche_number
        ? `&tranche=${props.shop.tranche_number}`
        : '')
    + `&focus-shop=${props.shop.id}`
  )
}

/* ===============================
   Helpers
================================ */
function formatMoney(value) {
    return new Intl.NumberFormat('de-DE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0))
}

/* ===============================
   Computed
================================ */
const total = computed(() => Number(props.summary?.total || 0))
const paid = computed(() => Number(props.summary?.paid || 0))
const remaining = computed(() => Number(props.summary?.remaining || 0))

const progress = computed(() => {
    if (!total.value) return 0
    return ((paid.value / total.value) * 100).toFixed(1)
})

/* 🔴 محمي من undefined */
const title = computed(() => {
    if (!props.shop) return ''

    let parts = [
        `بيان المحل رقم ${props.shop.number}`,
        `عمارة ${props.building_number}`,
    ]

    if (props.shop.tranche_number) {
        parts.push(`الشطر ${props.shop.tranche_number}`)
    }

    parts.push(`إقامة ${props.project.name}`)

    return parts.join(' - ')
})

/* ===============================
   Actions
================================ */

function openPdf() {
    const id = props.shop.id
    const url = `/shops/${id}/invoice/pdf`

    // فتح البيان في صفحة جديدة
    window.open(url, '_blank')
}

</script>

<template>
<AppLayout title="بيان المحل">
<div class="p-10">

<div v-if="shop">

<!-- ================== العنوان + رجوع ================== -->
<div class="flex items-center justify-between mb-8 border-b pb-3 px-6 print:hidden">
  <h1 class="text-2xl font-bold text-gray-800">
    {{ title }}
  </h1>

    <!-- زر الرجوع -->
    <button
        @click="goBack"
        class="flex items-center gap-2 px-6 py-2
               border-2 border-green-600 text-green-700
               rounded-xl hover:bg-green-50 transition
               font-bold shadow-sm"
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

<!-- ===== بطاقة معلومات المحل + الصورة ===== -->
<div class="bg-white rounded-2xl shadow p-6">

  <h3 class="text-lg font-bold mb-4 border-b pb-2">
    معلومات المحل
  </h3>

  <!-- GRID 2/3 + 1/3 -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

<!-- ================== المعلومات (⅔) ================== -->
<div class="lg:col-span-2 flex flex-col">

  <div class="grid grid-cols-2 gap-4 text-sm">

    <div>
      <p class="text-gray-500 font-bold">رقم المحل</p>
      <p class="font-semibold">{{ shop.number }}</p>
    </div>

    <div>
      <p class="text-gray-500 font-bold">المساحة</p>
      <p class="font-semibold">{{ shop.area }} م²</p>
    </div>

    <div>
      <p class="text-gray-500 font-bold">ثمن المتر</p>
      <p class="font-semibold">
        {{ formatMoney(shop.price_per_m2) }}
      </p>
    </div>

    <div>
      <p class="text-gray-500 font-bold">صاحب المحل</p>
      <p class="font-semibold">
        {{ shop.customer_name ?? '—' }}
      </p>
    </div>
    <!-- ===== MEZZANINE ===== -->
<div v-if="shop.mezzanine_area && shop.mezzanine_area > 0">
  <p class="text-gray-500 font-bold">الميزانين</p>
  <p class="font-semibold">
    {{ shop.mezzanine_area }} م² —
    {{ formatMoney(shop.mezzanine_total_price) }}
  </p>
</div>


    <!-- ===== التخفيض (إن وجد) ===== -->
    <div v-if="shop.discount && shop.discount > 0">
      <p class="text-gray-500 font-bold">قيمة التخفيض</p>
      <p class="font-semibold text-red-600">
        - {{ formatMoney(shop.discount) }}
      </p>
    </div>

  </div>

  <!-- ===== الثمن الإجمالي (بعد التخفيض – من DB) ===== -->
  <div class="mt-auto pt-4 border-t text-center">
    <p class="text-gray-500 text-sm font-bold">
      الثمن الإجمالي
    </p>
    <p class="text-xl font-bold text-green-600">
      {{ formatMoney(total) }}
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
    <template v-if="shop.image">
      <img
        :src="`/storage/${shop.image}`"
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

<!-- ================= الدفوعات + سجل الملكية ================= -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

<!-- ===== بطاقة الدفوعات ===== -->
<div class="bg-white rounded-2xl shadow p-6">
  <h3 class="text-lg font-bold mb-4 border-b pb-2">
    الدفوعات
  </h3>

  <table v-if="payments.length" class="w-full text-sm">
    <thead class="text-gray-500">
      <tr>
        <th class="text-right">التاريخ</th>
        <th class="text-right">المبلغ</th>
        <th class="text-right">النسبة</th>
        <th class="text-right">الطريقة</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="p in payments" :key="p.id" class="border-t">
        <td class="py-3">{{ p.paid_at }}</td>
        <td class="font-semibold text-green-600">
          {{ formatMoney(p.amount) }}
        </td>
        <td>
          <span
            class="px-2 py-1 rounded-full
                   bg-green-100 text-green-700
                   text-xs font-semibold"
          >
            {{ p.percentage }} %
          </span>
        </td>
        <td>{{ p.method }}</td>
      </tr>
    </tbody>
  </table>

  <p v-else class="text-center text-gray-400 py-10">
    لا توجد دفوعات بعد
  </p>
</div>
<!-- ===== بطاقة سجل الملكية ===== -->
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
            المالك الحالي
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
     العمود الجانبي (⅓)
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


<!-- ================== الأزرار ================== -->
<div class="flex flex-wrap justify-center gap-8 mt-10 print:hidden">

                <!-- تعديل -->
                <a
                    :href="`/shops/${shop.id}/edit`"
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
                    <span class="text-sm font-semibold text-gray-700">تعديل</span>
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
                    <span class="text-sm font-semibold text-gray-700">حذف</span>
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
                                  d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">PDF</span>
                </button>
                <!-- إضافة دفعة -->
                <a
                    :href="`/shops/${shop.id}/payments/create`"
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
                    <span class="text-sm font-semibold text-gray-700">إضافة دفعة</span>
                </a>
                <!-- تسجيل تنازل -->
                <a
    :href="`/transfers/create/shop/${shop.id}`"
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
</div>

<!-- ===== Modal حذف ===== -->
<div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-3xl p-8 max-w-lg w-full text-center">

    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف المحل
    </h2>

    <p class="text-gray-700 mb-6">
      هل تريد حذف المحل رقم
      <strong>{{ shop.number }}</strong>
      بعمارة
      <strong>{{ shop.building.name }}</strong>
      ضمن إقامة
      <strong>{{ project.name }}</strong>؟
    </p>

    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      هذا الإجراء نهائي ولا يمكن التراجع عنه
    </div>

    <div class="flex justify-center gap-4">
      <button
        @click="showDeleteModal = false"
        class="px-6 py-2 border rounded-xl"
      >
        إلغاء
      </button>

      <button
        @click="remove"
        :disabled="deleting"
        class="px-6 py-2 bg-red-600 text-white rounded-xl"
      >
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف' }}
      </button>
    </div>
  </div>
</div>

</div>
</div>
</AppLayout>
</template>

