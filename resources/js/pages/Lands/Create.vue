<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

/* ================= PROPS ================= */
const props = defineProps({
  projects: Array,
})

/* ================= STATE ================= */
const activeStep = ref(1)
const imagePreview = ref(null)

/* ================= FORM ================= */
const form = useForm({
  project_id: '',
  land_number: '',
  road_type: '',
  view_type: '1-FACADE',

  area: '',
  price_per_m2: '',
  discount: 0,

  status: 'متاحة',

  customer_name: '',
  customer_id: '',
  customer_phone: '',

  image: null,
})

/* ================= IMAGE ================= */
function handleImage(e) {
  const file = e.target.files[0]
  if (!file) return

  form.image = file
  imagePreview.value = URL.createObjectURL(file)
}

/* ================== Helpers ================== */
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

const totalPrice = computed(() =>
  Math.max(baseTotal.value - Number(form.discount || 0), 0)
)

/* ================= SUBMIT ================= */
function submit() {
  form.post('/lands', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
<AppLayout title="إضافة قطعة أرضية">
<form @submit.prevent="submit">

<div class="p-8">
  <h1 class="text-2xl font-bold text-green-700 mb-6 text-right">
    إضافة قطعة أرضية
  </h1>

  <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- ================= LEFT ================= -->
    <div class="lg:col-span-3 space-y-6">

      <!-- ===== STEPPER ===== -->
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
    <!-- أيقونة قطعة أرض -->
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M3 6l9-4 9 4v12l-9 4-9-4V6zm9-1.8L5 6.8v9.4l7 3.1 7-3.1V6.8l-7-2.6z"/>
    </svg>
  </span>
  معلومات القطعة
</button>

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
    <!-- أيقونة التخفيض -->
    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor">
      <path fill-rule="evenodd" clip-rule="evenodd"
        d="M7.25 10.5a3.75 3.75 0 1 1 0-7.5
           3.75 3.75 0 0 1 0 7.5zm-1.543 9.207
           a1 1 0 0 1-1.414-1.414l14-14
           a1 1 0 1 1 1.414 1.414l-14 14z
           M13 17.25a3.75 3.75 0 1 0 7.5 0
           3.75 3.75 0 0 0-7.5 0z"/>
    </svg>
  </span>
  التخفيض
</button>


      </div>

      <!-- ===== FORM CARD ===== -->
      <div class="bg-white rounded-2xl shadow p-8 min-h-[320px]">

        <!-- STEP 1 -->
        <div v-show="activeStep === 1" class="space-y-8">

          <h2 class="font-bold">معلومات القطعة</h2>

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
              <label class="font-semibold">رقم القطعة</label>
              <input v-model="form.land_number" class="border rounded-xl p-3 w-full" />
            </div>

            <div>
              <label class="font-semibold">نوع الطريق</label>
              <input v-model="form.road_type" class="border rounded-xl p-3 w-full" />
            </div>

            <div>
              <label class="font-semibold">الواجهة</label>
              <select v-model="form.view_type" class="border rounded-xl p-3 w-full">
                <option value="1-FACADE">1-FACADE</option>
                <option value="2-FACADE">2-FACADE</option>
              </select>
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
                <option>متاحة</option>
                <option>محجوزة</option>
                <option>مباعة</option>
              </select>
            </div>

          </div>

          <!-- CUSTOMER -->
          <div>
            <h3 class="font-bold mb-4">صاحب القطعة</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <input v-model="form.customer_name" placeholder="اسم صاحب القطعة" class="border rounded-xl p-3" />
              <input v-model="form.customer_id" placeholder="رقم التعريف" class="border rounded-xl p-3" />
              <input v-model="form.customer_phone" placeholder="رقم الهاتف" class="border rounded-xl p-3" />
            </div>
          </div>

        </div>

        <!-- STEP 2 -->
        <div v-show="activeStep === 2" class="space-y-4">
          <h2 class="font-bold">التخفيض</h2>
          <input
            v-model="form.discount"
            type="number"
            class="border rounded-xl p-3 w-full max-w-sm"
          />
        </div>

      </div>
    </div>

    <!-- ================= RIGHT ================= -->
    <div class="space-y-6">

      <!-- IMAGE -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="font-bold mb-3">صورة القطعة</h3>
        <label class="border-2 border-dashed h-56 flex items-center justify-center cursor-pointer rounded-xl">
          <input
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleImage"
          />
          <span v-if="!imagePreview">اضغط لإضافة صورة</span>
          <img v-else :src="imagePreview" class="max-h-40 object-contain" />
        </label>
      </div>

      <!-- SUMMARY -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="font-bold mb-4">ملخص السعر</h3>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span>ثمن القطعة</span>
            <span>{{ formatMoney(baseTotal )}}</span>
          </div>

          <div
            v-if="Number(form.discount) > 0"
            class="flex justify-between text-red-600"
          >
            <span>قيمة التخفيض</span>
            <span>- {{ formatMoney(form.discount)}}</span>
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
      حفظ القطعة
    </button>
  </div>

</div>
</form>
</AppLayout>
</template>
