<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

/* ================= PROPS ================= */
const props = defineProps({
  shop: Object,
  projects: Array,
})

/* ================= STATE ================= */
const activeStep = ref(1)
const imagePreview = ref(props.shop.image_url ?? null)

/* ================= FORM ================= */
const form = useForm({
  project_id: props.shop.project_id ?? '',
  building_number: props.shop.building_number ?? '',
  number: props.shop.number ?? '',
  tranche_number: props.shop.tranche_number ?? '',

  area: props.shop.area ?? '',
  price_per_m2: props.shop.price_per_m2 ?? '',
  status: props.shop.status ?? 'متاح',

  /* 🪜 Mezzanine */
  mezzanine_area: props.shop.mezzanine_area ?? '',
  mezzanine_price_per_m2: props.shop.mezzanine_price_per_m2 ?? '',

  customer_name: props.shop.customer_name ?? '',
  customer_id: props.shop.customer_id ?? '',
  customer_phone: props.shop.customer_phone ?? '',

  discount: props.shop.discount ?? 0,
  image: null,
})

/* ================= IMAGE ================= */
function handleImage(e) {
  const file = e.target.files[0]
  if (!file) return
  form.image = file
  imagePreview.value = URL.createObjectURL(file)
}

/* ================= HELPERS ================= */
function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

/* ================= CALCULATIONS ================= */
const baseTotal = computed(() =>
  Number(form.area || 0) * Number(form.price_per_m2 || 0)
)

const mezzanineTotal = computed(() =>
  Number(form.mezzanine_area || 0) *
  Number(form.mezzanine_price_per_m2 || 0)
)

const totalPrice = computed(() =>
  Math.max(
    baseTotal.value +
    mezzanineTotal.value -
    Number(form.discount || 0),
    0
  )
)

/* ================= SUBMIT ================= */
function submit() {
  form
    .transform(data => ({
      ...data,
      _method: 'patch',
    }))
    .post(`/shops/${props.shop.id}`, {
      forceFormData: true,
      preserveScroll: true,
    })
}
</script>

<template>
<AppLayout title="تعديل محل تجاري">

<form @submit.prevent="submit" class="p-8">

<h1 class="text-2xl font-bold text-green-700 mb-6 text-right">
  تعديل محل تجاري
</h1>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-6">

<!-- ================= LEFT ================= -->
<div class="lg:col-span-3 space-y-6">

<!-- ===== STEPPER (نفس Create) ===== -->
<div class="bg-white rounded-2xl shadow px-8 py-5 flex items-center gap-8">

<button
  type="button"
  @click="activeStep = 1"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 1 ? 'text-green-700' : 'text-gray-400'"
>
  <span
    class="w-10 h-10 rounded-full flex items-center justify-center"
    :class="activeStep === 1 ? 'bg-green-100' : 'bg-gray-100'"
  >
    <!-- أيقونة معلومات المحل -->
    <svg
      viewBox="0 0 24 24"
      class="w-5 h-5"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M3.77791 3.65484C3.59687 4.01573 3.50783 4.46093 3.32975 5.35133L2.73183 8.34093C2.35324 10.2339 3.8011 12 5.73155 12C7.30318 12 8.61911 10.8091 8.77549 9.24527L8.8445 8.55515C8.68141 10.4038 10.1385 12 11.9998 12C13.8737 12 15.338 10.382 15.1515 8.51737L15.2245 9.24527C15.3809 10.8091 16.6968 12 18.2685 12C20.1989 12 21.6468 10.2339 21.2682 8.34093L20.6703 5.35133C20.4922 4.46095 20.4031 4.01573 20.2221 3.65484C19.8406 2.89439 19.1542 2.33168 18.3337 2.10675C17.9443 2 17.4903 2 16.5823 2H14.4998H7.41771C6.50969 2 6.05567 2 5.66628 2.10675C4.84579 2.33168 4.15938 2.89439 3.77791 3.65484Z"
      />
      <path
        d="M18.2685 13.5C19.0856 13.5 19.8448 13.2876 20.5 12.9189V14C20.5 17.7712 20.5 19.6568 19.3284 20.8284C18.3853 21.7715 16.9796 21.9554 14.5 21.9913V18.5C14.5 17.5654 14.5 17.0981 14.299 16.75C14.1674 16.522 13.978 16.3326 13.75 16.201C13.4019 16 12.9346 16 12 16C11.0654 16 10.5981 16 10.25 16.201C10.022 16.3326 9.83261 16.522 9.70096 16.75C9.5 17.0981 9.5 17.5654 9.5 18.5V21.9913C7.02043 21.9554 5.61466 21.7715 4.67157 20.8284C3.5 19.6568 3.5 17.7712 3.5 14V12.9189C4.15524 13.2876 4.91439 13.5 5.73157 13.5C6.92864 13.5 8.02617 13.0364 8.84435 12.2719C9.67168 13.0321 10.7765 13.5 11.9998 13.5C13.2232 13.5 14.3281 13.032 15.1555 12.2717C15.9737 13.0363 17.0713 13.5 18.2685 13.5Z"
      />
    </svg>
  </span>
  معلومات المحل
</button>

<!-- STEP 2 : MEZZANINE -->
<button
  type="button"
  @click="activeStep = 2"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 2 ? 'text-green-700' : 'text-gray-400'"
>
  <span
    class="w-10 h-10 rounded-full flex items-center justify-center"
    :class="activeStep === 2 ? 'bg-green-100' : 'bg-gray-100'"
  >
    <!-- أيقونة الميزانين -->
    <svg
      viewBox="0 0 512 512"
      class="w-5 h-5"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <polygon
        points="354.38,53.422 354.38,168.726 226.378,168.726
                226.378,284.03 98.38,284.03 98.38,399.334
                0,399.334 0,458.578 157.62,458.578
                157.62,343.274 285.622,343.274
                285.622,227.97 413.625,227.97
                413.625,112.666 512,112.666 512,53.422"
      />
    </svg>
  </span>
  Mezzanine
</button>

<button type="button" @click="activeStep = 3"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 3 ? 'text-green-700' : 'text-gray-400'">
  <span class="w-10 h-10 rounded-full flex items-center justify-center"
        :class="activeStep === 3 ? 'bg-green-100' : 'bg-gray-100'">
    %
  </span>
  التخفيض
</button>

</div>

<!-- ===== FORM CARD ===== -->
<div class="bg-white rounded-2xl shadow p-8 min-h-[320px]">

<!-- STEP 1 -->
<div v-show="activeStep === 1" class="space-y-8">

<h2 class="font-bold">معلومات المحل</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

<div>
  <label class="font-semibold">المشروع</label>
  <select v-model="form.project_id" class="border rounded-xl p-3 w-full">
    <option value="">اختر</option>
    <option v-for="p in projects" :key="p.id" :value="p.id">
      {{ p.name }}
    </option>
  </select>
</div>

<div>
  <label class="font-semibold">رقم العمارة</label>
  <input v-model="form.building_number" class="border rounded-xl p-3 w-full" />
</div>

<div>
  <label class="font-semibold">رقم المحل</label>
  <input v-model="form.number" class="border rounded-xl p-3 w-full" />
</div>

<div>
  <label class="font-semibold">رقم الشطر</label>
  <input v-model="form.tranche_number" class="border rounded-xl p-3 w-full" />
</div>

<div>
  <label class="font-semibold">المساحة (م²)</label>
  <input v-model="form.area" type="number" class="border rounded-xl p-3 w-full" />
</div>

<div>
  <label class="font-semibold">ثمن المتر</label>
  <input v-model="form.price_per_m2" type="number" class="border rounded-xl p-3 w-full" />
</div>

<div>
  <label class="font-semibold">الحالة</label>
  <select v-model="form.status" class="border rounded-xl p-3 w-full">
    <option>متاح</option>
    <option>محجوز</option>
    <option>مباع</option>
  </select>
</div>

</div>

<!-- صاحب المحل -->
<div>
  <h3 class="font-bold mb-4">صاحب المحل</h3>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <input v-model="form.customer_name" placeholder="اسم صاحب المحل" class="border rounded-xl p-3" />
    <input v-model="form.customer_id" placeholder="رقم التعريف" class="border rounded-xl p-3" />
    <input v-model="form.customer_phone" placeholder="رقم الهاتف" class="border rounded-xl p-3" />
  </div>
</div>

</div>

<!-- STEP 2 -->
<div v-show="activeStep === 2" class="space-y-8">
  <h2 class="font-bold">معلومات الميزانين</h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-3xl">
    <input v-model="form.mezzanine_area" type="number"
      placeholder="مساحة الميزانين (م²)" class="border rounded-xl p-3" />

    <input v-model="form.mezzanine_price_per_m2" type="number"
      placeholder="ثمن متر الميزانين" class="border rounded-xl p-3" />
  </div>
</div>

<!-- STEP 3 -->
<div v-show="activeStep === 3" class="space-y-4">
  <h2 class="font-bold">التخفيض</h2>
  <input v-model="form.discount" type="number"
    class="border rounded-xl p-3 w-full max-w-sm" />
</div>

</div>
</div>

<!-- ================= RIGHT ================= -->
<div class="space-y-6">

<!-- IMAGE -->
<div class="bg-white rounded-2xl shadow p-6">
  <h3 class="font-bold mb-3">صورة المحل</h3>
  <label class="border-2 border-dashed h-56 flex items-center justify-center cursor-pointer rounded-xl">
    <input type="file" class="hidden" @change="handleImage" />
    <img v-if="imagePreview" :src="imagePreview" class="max-h-40 object-contain" />
    <span v-else class="text-gray-400">اضغط لتغيير الصورة</span>
  </label>
</div>

<!-- SUMMARY -->
<div class="bg-white rounded-2xl shadow p-6">
  <h3 class="font-bold mb-4">ملخص السعر</h3>

  <div class="space-y-2 text-sm">
    <div class="flex justify-between">
      <span>ثمن المحل</span>
      <span>{{ formatMoney(baseTotal) }}</span>
    </div>

    <div v-if="mezzanineTotal > 0" class="flex justify-between">
      <span>ثمن MEZZANINE</span>
      <span>{{ formatMoney(mezzanineTotal) }}</span>
    </div>

    <div v-if="Number(form.discount) > 0" class="flex justify-between text-red-600">
      <span>قيمة التخفيض</span>
      <span>- {{ formatMoney(form.discount) }}</span>
    </div>
  </div>

  <div class="border-t mt-4 pt-3 flex justify-between font-bold text-green-700">
    <span>الثمن الإجمالي</span>
    <span>{{ formatMoney(totalPrice) }} درهم</span>
  </div>
</div>

</div>
</div>

<div class="mt-6 bg-white p-6 rounded-2xl shadow max-w-7xl mx-auto">
  <button type="submit" class="bg-green-700 text-white px-10 py-3 rounded-xl">
    حفظ تعديلات المحل
  </button>
</div>

</form>
</AppLayout>
</template>
