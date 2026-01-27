<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed , onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

/* ===============================
   Props القادمة من Laravel
================================ */
const props = defineProps({
    apartment: Object,
    project: Object,
    building_number: String,

    summary: {
        type: Object,
        default: () => ({
            total: 0,
            paid: 0,
            remaining: 0,
        }),
    },

    payments: {
        type: Array,
        default: () => [],
    },
      ownership_history: {
    type: Array,
    default: () => [],
  },
})
const title = computed(() => {
    let parts = [
        `بيان الشقة رقم ${props.apartment.number}`,
        `عمارة ${props.building_number}`,
    ]

    // ✅ الاسم الصحيح للحقل
    if (props.apartment.tranche_number) {
        parts.push(`الشطر ${props.apartment.tranche_number}`)
    }

    parts.push(`إقامة ${props.project.name}`)

    return parts.join(' – ')
})

/* -----------------------------------------------------
   PDF
----------------------------------------------------- */
function openPdf() {
    const id = props.apartment.id;
    const url = `/apartments/${id}/invoice/pdf`;

    // فتح نافذة جديدة
    window.open(url, "_blank");


}

/* -----------------------------------------------------
   Model التنازل
----------------------------------------------------- */

const page = usePage()
const showTransferErrorModal = ref(false)

onMounted(() => {
  if (page.props.flash?.error) {
    showTransferErrorModal.value = true
  }
})




/* -----------------------------------------------------
   حذف الشقة —  
----------------------------------------------------- */
const deleting = ref(false)
const showDeleteModal = ref(false)
function confirmDelete() {
  showDeleteModal.value = true
}

function remove() {
  deleting.value = true

  router.delete(`/apartments/${props.apartment.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
    },
  })
}

/* ===============================
   حسابات آمنة 100%
================================ */
const total = computed(() => Number(props.summary?.total || 0))
const paid = computed(() => Number(props.summary?.paid || 0))
const remaining = computed(() => Number(props.summary?.remaining || 0))

const progress = computed(() => {
    if (!total.value) return 0
    return ((paid.value / total.value) * 100).toFixed(1)
})
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}
function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR')
}

</script>

<template>
  <AppLayout title="عرض الشقة">
    <div class="p-10">

      <!-- ================== العنوان + زر الرجوع ================== -->
      <div class="flex items-center justify-between mb-8 border-b pb-3 px-6">

        <h1 class="text-2xl font-bold text-gray-800">
          {{ title }}
        </h1>

        <button
          @click="router.visit(`/projects/${project.id}/apartments?focus=${apartment.id}`)"
          class="print:hidden flex items-center gap-2 px-6 py-2
                 border-2 border-green-600 text-green-700 rounded-xl
                 hover:bg-green-50 transition font-bold shadow-sm"
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
             العمود الرئيسي (⅔) : معلومات الشقة + الدفوعات
        ====================================================== -->
        <div class="lg:col-span-2 flex flex-col gap-6">

<!-- ===== بطاقة معلومات الشقة ===== -->
<div class="bg-white rounded-2xl shadow p-6">

  <h3 class="text-lg font-bold mb-4 border-b pb-2">
    معلومات الشقة
  </h3>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

    <!-- ================== المعلومات (⅔) ================== -->
    <div class="lg:col-span-2 flex flex-col">

      <div class="grid grid-cols-2 gap-4 text-sm">

        <!-- الطابق -->
        <div>
          <p class="text-gray-500 font-bold">الطابق</p>
          <p class="font-semibold">{{ apartment.floor }}</p>
        </div>

        <!-- عدد الغرف -->
        <div>
          <p class="text-gray-500 font-bold">عدد الغرف</p>
          <p class="font-semibold">{{ apartment.rooms }}</p>
        </div>

        <!-- المساحة + التيراس (سطر واحد) -->
        <div>
            <p class="text-gray-500 font-bold">المساحة</p>
            <p class="font-semibold">
                {{ apartment.area }} م²
                <template v-if="apartment.has_terrace">
                    + {{ apartment.terrace_area }} م²
                    <span class="text-gray-500 text-xs">
                        ({{ apartment.terrace_type }})
                    </span>
                    <span class="text-green-600 font-semibold">
                         {{ formatMoney(apartment.terrace_total_price) }}
                    </span>
                </template>
            </p>
        </div>

        <!-- صاحب الشقة -->
        <div>
          <p class="text-gray-500 font-bold">صاحب الشقة</p>
          <p class="font-semibold">
            {{ apartment.customer_name ?? '—' }}
          </p>
        </div>

        <!-- موقف السيارة (رقم + ثمن في سطر واحد) -->
        <template v-if="apartment.has_parking">
            <div>
                <p class="text-gray-500 font-bold">موقف السيارة</p>
                <p class="font-semibold">
                    رقم {{ apartment.parking_number }}
                    <span class="text-green-600 font-semibold">
                         {{ formatMoney(apartment.parking_price) }}
                    </span>
                </p>
            </div>
        </template>


        <!-- التخفيض (إن وجد) -->
        <div v-if="apartment.discount && apartment.discount > 0">
          <p class="text-gray-500 font-bold">قيمة التخفيض</p>
          <p class="font-semibold text-red-600">
            - {{ formatMoney(apartment.discount) }}
          </p>
        </div>

      </div>

      <!-- ===== الثمن الإجمالي ===== -->
      <div class="mt-auto pt-4 border-t text-center">
        <p class="text-gray-500 text-sm font-bold">الثمن الإجمالي</p>
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
        <template v-if="apartment.image">
          <img
            :src="`/storage/${apartment.image}`"
            class="w-full h-full object-cover"
          />
        </template>

        <template v-else>
          <div class="text-gray-400 text-sm flex flex-col items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-10 h-10 opacity-40"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
          <div class="bg-white rounded-2xl shadow p-6 flex flex-col h-full">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">
              الدفوعات
            </h3>

            <table v-if="payments.length" class="w-full text-sm">
              <thead class="text-gray-500">
                <tr>
                  <th class="text-right py-2">التاريخ</th>
                  <th class="text-right">المبلغ</th>
                  <th class="text-right">النسبة</th>
                  <th class="text-right">الطريقة</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="p in payments"
                  :key="p.id"
                  class="border-t"
                >
                  <td class="py-3">{{ formatDate(p.paid_at) }}</td>
                  <td class="font-semibold text-green-600">
                    {{ formatMoney(p.amount) }}
                  </td>
                  <td>
                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
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


  <!-- ================== الأزرار (تحت الملخص) ================== -->
 <!-- ================== الأزرار ================== -->
        <div class="flex flex-wrap justify-center gap-8 mt-10 print:hidden">

    <!-- تعديل -->
    <a
        :href="`/apartments/${apartment.id}/edit`"
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
        :href="`/apartments/${apartment.id}/payments/create`"
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
    :href="`/transfers/create/apartment/${apartment.id}`"
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



    <!-- Delete Apartment Modal -->
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
      تأكيد حذف الشقة
    </h2>

    <!-- الجملة -->
<p class="text-gray-700 text-lg leading-relaxed mb-6">
  هل تريد حذف
  <span class="font-bold text-gray-900">
    الشقة رقم {{ apartment.number }}
  </span>
  عمارة
  <span class="font-bold text-gray-900">
    {{ apartment.building?.name }}
  </span>
بإقامة  <span class="font-bold text-gray-900">
    {{ apartment.building.project.name }}
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
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف الشقة' }}
      </button>

    </div>
 </div>
     </div>
<!-- ===== مودال خطأ تسجيل التنازل ===== -->
<div
  v-if="showTransferErrorModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div
      class="mx-auto mb-4 w-14 h-14 flex items-center justify-center
             rounded-full bg-orange-100"
    >
      <span class="text-3xl text-orange-600">⚠️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-orange-600 mb-4">
      لا يمكن تسجيل تنازل
    </h2>

    <!-- الرسالة -->
    <p class="text-gray-700 text-lg leading-relaxed mb-6">
      لا يمكن تسجيل تنازل لهذه الوحدة لأنها
      <span class="font-bold text-gray-900">
        لا تملك مالكًا حاليًا
      </span>
      .
      <br />
      يرجى التأكد من تسجيل المالك أولًا.
    </p>

    <!-- تنبيه -->
    <div
      class="bg-orange-50 text-orange-700 text-sm rounded-xl
             px-4 py-3 mb-6"
    >
      لا يمكن إتمام عملية التنازل بدون وجود مالك حالي للوحدة
    </div>

    <!-- زر -->
    <div class="flex justify-center">
      <button
        @click="showTransferErrorModal = false"
        class="px-10 py-3 rounded-xl
               bg-green-600 text-white font-bold
               hover:bg-green-700 transition"
      >
        حسنًا
      </button>
    </div>

  </div>
</div>


</div>
    </div>
  </AppLayout>
</template>

