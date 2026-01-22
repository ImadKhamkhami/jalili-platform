<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

/* ================= PROPS ================= */
const props = defineProps({
  payment: Object,
  project: Object,
  unit: Object,
  summary: Object,
  context: String,
})

/* ================= FORMAT MONEY ================= */
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value ?? 0)
}

/* ================= FORM ================= */
const form = useForm({
  amount: props.payment.amount,
  payment_method: props.payment.payment_method,
  paid_at: props.payment.paid_at,
})

/* ================= COMPUTED ================= */
const total = computed(() => props.summary?.total ?? 0)
const paid = computed(() => props.summary?.paid ?? 0)
const remainingAmount = computed(() => props.summary?.remaining ?? 0)

/* ================= VALIDATION ================= */
const amountTouched = ref(false)

const isAmountInvalid = computed(() => {
  if (!amountTouched.value) return false

  const amount = Number(form.amount || 0)
  return amount <= 0 || amount > remainingAmount.value
})

/* ================= SUBMIT ================= */
function submit() {
  amountTouched.value = true
  if (isAmountInvalid.value) return

  form.put(`/payments/${props.payment.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout title="تعديل الدفعة">

    <div class="flex justify-center py-10">
      <div class="bg-white rounded-3xl shadow-lg p-10 w-full max-w-3xl">

        <h1 class="text-center text-2xl font-bold text-green-700 mb-10">
          تعديل الدفعة
        </h1>

        <!-- بطاقة معلومات الوحدة (مطابقة لـ CREATE) -->
<!-- بطاقة معلومات الوحدة -->
<div
  class="bg-gray-50 rounded-2xl p-6 grid grid-cols-2 md:grid-cols-3 gap-6 mb-10 text-right"
>

  <!-- المشروع -->
  <div>
    <p class="text-sm text-gray-500">المشروع</p>
    <p class="font-semibold">{{ project?.name }}</p>
  </div>

  <!-- رقم الشقة / المحل / القطعة -->
  <div>
    <p class="text-sm text-gray-500">
      {{
        context === 'apartment'
          ? 'رقم الشقة'
          : context === 'shop'
            ? 'رقم المحل'
            : 'رقم القطعة'
      }}
    </p>
    <p class="font-semibold">{{ unit?.number }}</p>
  </div>

  <!-- صاحب الوحدة -->
  <div>
    <p class="text-sm text-gray-500">
      {{
        context === 'apartment'
          ? 'صاحب الشقة'
          : context === 'shop'
            ? 'صاحب المحل'
            : 'صاحب القطعة'
      }}
    </p>
    <p class="font-semibold">{{ unit?.owner_name }}</p>
  </div>

  <!-- المساحة -->
  <div>
    <p class="text-sm text-gray-500">المساحة</p>
    <p class="font-semibold">{{ unit?.area }} م²</p>
  </div>

  <!-- 🏢 الطابق (شقق فقط) -->
  <div v-if="context === 'apartment'">
    <p class="text-sm text-gray-500">الطابق</p>
    <p class="font-semibold">{{ unit?.floor }}</p>
  </div>

  <!-- 🛏️ عدد الغرف (شقق فقط) -->
  <div v-if="context === 'apartment'">
    <p class="text-sm text-gray-500">عدد الغرف</p>
    <p class="font-semibold">{{ unit?.rooms }}</p>
  </div>

  <!-- ثمن المتر -->
  <div>
    <p class="text-sm text-gray-500">ثمن المتر</p>
    <p class="font-semibold text-green-600">
      {{ formatMoney(unit?.price_per_m2) }}
    </p>
  </div>

  <!-- 🚗 موقف السيارة (شقق فقط إن وجد) -->
  <div v-if="context === 'apartment' && unit?.has_parking">
    <p class="text-sm text-gray-500">موقف السيارة</p>
    <p class="font-semibold">
      رقم {{ unit?.parking_number }}
      <span class="text-green-600">
        ({{ formatMoney(unit?.parking_price) }})
      </span>
    </p>
  </div>

  <!-- 🌿 التيراس (شقق فقط إن وجد) -->
  <div v-if="context === 'apartment' && unit?.has_terrace">
    <p class="text-sm text-gray-500">
      {{ unit?.terrace_type }}
    </p>
    <p class="font-semibold">
      {{ unit?.terrace_area }} م²
      <span class="text-green-600">
        ({{ formatMoney(unit?.terrace_total_price) }})
      </span>
    </p>
  </div>

</div>


        <!-- الملخص -->
        <div class="grid grid-cols-3 text-center bg-gray-50 rounded-2xl py-6 mb-10">
          <div>
            <div class="text-gray-500">الإجمالي</div>
            <div class="font-bold">{{ formatMoney(total) }}</div>
          </div>
          <div>
            <div class="text-gray-500">المدفوع</div>
            <div class="font-bold text-green-600">{{ formatMoney(paid) }}</div>
          </div>
          <div>
            <div class="text-gray-500">المتبقي</div>
            <div class="font-bold text-red-600">{{ formatMoney(remainingAmount) }}</div>
          </div>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit">

          <!-- طريقة الدفع -->
          <div class="mb-8 text-right">
            <div class="font-semibold mb-4">طريقة الدفع</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <label
                v-for="m in ['cash','check','transfer','bill']"
                :key="m"
                class="cursor-pointer border rounded-xl p-4 text-center"
                :class="form.payment_method === m
                  ? 'border-green-600 bg-green-50'
                  : 'border-gray-200'"
              >
                <input type="radio" :value="m" v-model="form.payment_method" class="hidden">
                {{ m === 'cash' ? 'نقدًا'
                  : m === 'check' ? 'شيك'
                  : m === 'transfer' ? 'تحويل'
                  : 'كمبيالة' }}
              </label>
            </div>
          </div>

          <!-- المبلغ + التاريخ -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

            <!-- المبلغ -->
<div>
  <label class="font-semibold mb-2 block text-gray-700">المبلغ</label>

  <div class="relative">
    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">
      د.م
    </span>

    <input
      type="number"
      step="0.01"
      v-model.number="form.amount"
      @input="amountTouched = true"
      class="w-full rounded-xl pl-14 pr-4 py-3 text-lg font-semibold
             border-2 border-gray-300 bg-white
             focus:border-green-600 focus:ring-2 focus:ring-green-200 transition"
      :class="isAmountInvalid
        ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
        : ''"
    />
  </div>

  <p class="text-sm mt-2 text-gray-500">
    المتبقي: {{ formatMoney(remainingAmount) }} د.م
  </p>

  <p v-if="isAmountInvalid" class="text-sm text-red-600 mt-1 font-semibold">
    المبلغ المدخل أكبر من المتبقي
  </p>
</div>


            <!-- التاريخ -->
            <div>
              <label class="font-semibold mb-2 block">تاريخ الدفع</label>
              <input
                type="date"
                v-model="form.paid_at"
                class="w-full rounded-xl px-4 py-3 border-2 border-gray-300"
              />
            </div>

          </div>

<button
  type="submit"
  :disabled="isAmountInvalid || form.processing"
  class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-2xl font-bold
         disabled:opacity-50 disabled:cursor-not-allowed transition"
>
  حفظ التعديلات
</button>


        </form>

      </div>
    </div>

  </AppLayout>
</template>
