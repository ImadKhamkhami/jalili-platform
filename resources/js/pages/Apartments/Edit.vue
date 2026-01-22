<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  projects: Array,
  apartment: Object,
})

const activeStep = ref(1)
const imagePreview = ref(props.apartment.image_url ?? null)

/* =====================================================
   ✅ FORM (المكان الصحيح لـ _method)
===================================================== */
const form = useForm({
  _method: 'patch', // ✅ ضروري هنا

  project_id: props.apartment?.project_id ?? '',
  building_number: props.apartment?.building_number ?? '',

  number: props.apartment?.number ?? '',
  floor: props.apartment?.floor ?? 0,
  tranche_number: props.apartment?.tranche_number ?? '',
  rooms: props.apartment?.rooms ?? 2,
  area: props.apartment?.area ?? '',
  price_per_m2: props.apartment?.price_per_m2 ?? '',
  status: props.apartment?.status ?? 'متاحة',

  customer_name: props.apartment?.customer_name ?? '',
  customer_id: props.apartment?.customer_id ?? '',
  customer_phone: props.apartment?.customer_phone ?? '',

  parking_number: props.apartment?.parking_number ?? '',
  parking_price: props.apartment?.parking_price ?? '',

  terrace_type: props.apartment?.terrace_type ?? '',
  terrace_area: props.apartment?.terrace_area ?? '',

  discount: props.apartment?.discount ?? '',
  image: null,
})

/* =====================================================
   CALCULATIONS
===================================================== */
const baseTotal = computed(() =>
  Number(form.area || 0) * Number(form.price_per_m2 || 0)
)

const terraceTotal = computed(() =>
  form.terrace_area
    ? Number(form.terrace_area) * (Number(form.price_per_m2 || 0) / 2)
    : 0
)

const totalPrice = computed(() =>
  Math.max(
    baseTotal.value +
      (form.parking_price ? Number(form.parking_price) : 0) +
      terraceTotal.value -
      (form.discount ? Number(form.discount) : 0),
    0
  )
)

function formatMoney(value) {
  return new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

/* =====================================================
   IMAGE
===================================================== */
function handleImage(e) {
  const file = e.target.files[0]
  if (!file) return
  form.image = file
  imagePreview.value = URL.createObjectURL(file)
}

/* =====================================================
   SUBMIT (بدون _method هنا)
===================================================== */
function submit() {
  form.post(`/apartments/${props.apartment.id}`, {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>


<template>
<AppLayout title="إضافة شقة جديدة">
  <form @submit.prevent="submit">

<div class="p-8">

<h1 class="text-2xl font-bold text-green-700 mb-6 text-right">
  تعديل شقة 
</h1>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-6">

<!-- ================= LEFT ================= -->
<div class="lg:col-span-3 space-y-6">

<!-- ===== STEPPER (نفس تصميمك – بدون أي تغيير) ===== -->
<div
  class="bg-white rounded-2xl shadow px-8 py-5
         flex flex-wrap items-center justify-between gap-6">

<!-- STEP 1 -->
<button type="button" @click="activeStep = 1"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 1 ? 'text-green-700' : 'text-gray-400'">
  <span class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 3l9 8h-3v10h-5v-6H11v6H6V11H3l9-8z"/>
    </svg>
  </span>
  معلومات الشقة
</button>

<!-- STEP 2 -->
<button type="button" @click="activeStep = 2"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 2 ? 'text-green-700' : 'text-gray-400'">
  <span class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100">
    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor">
      <path d="M16,6l3,4h2c1.11,0,2,0.89,2,2v3h-2c0,1.66-1.34,3-3,3s-3-1.34-3-3H9c0,1.66-1.34,3-3,3s-3-1.34-3-3H1v-3c0-1.11,0.89-2,2-2l3-4H16z"/>
    </svg>
  </span>
  موقف السيارة
</button>

<!-- STEP 3 -->
<button type="button" @click="activeStep = 3"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 3 ? 'text-green-700' : 'text-gray-400'">
  
  <span class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100">
    <svg
      viewBox="0 0 496 496"
      class="w-5 h-5"
      fill="currentColor"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M464,96V64h-32V32h-32V0H0v400h32v32h32v32h32v32h400V96H464z
               M16,384V16h368v368H16z
               M480,480H112v-16h352V320h-16v128H80v-16h352V232h-16v184H48v-16h352V48h16v168h16V80h16v224h16V112h16V480z"/>
      <path d="M128,32H32v96h96V32z M112,112H48V48h64V112z"/>
      <path d="M248,32h-96v96h96V32z M232,112h-64V48h64V112z"/>
      <path d="M272,128h96V32h-96V128z M288,48h64v64h-64V48z"/>
      <path d="M190.352,144L72,194.728V240h24v128h208V240h24v-45.272L209.64,144H190.352z
               M240,352h-80v-40c0-22.056,17.944-40,40-40s40,17.944,40,40V352z
               M288,352h-32v-40c0-30.88-25.12-56-56-56s-56,25.12-56,56v40h-32V240h176V352z
               M312,224H96v-18.728L193.64,160h12.72L312,205.272V224z"/>
    </svg>
  </span>

  Terrasse / Coeur
</button>

<!-- STEP 4 -->
<button type="button" @click="activeStep = 4"
  class="flex items-center gap-3 font-semibold min-w-[200px]"
  :class="activeStep === 4 ? 'text-green-700' : 'text-gray-400'">
  <span class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100">
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
  تخفيض
</button>

</div>

<!-- ===== FORM CARD ===== -->
<div class="bg-white rounded-2xl shadow p-8 min-h-[360px]">

<!-- STEP 1 -->
<div v-show="activeStep === 1" class="space-y-8">
  <h2 class="font-bold">معلومات الشقة</h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>
      <label class="font-semibold">المشروع</label>
      <select v-model="form.project_id" class="border rounded-xl p-3 w-full">
        <option value="">اختر</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <p v-if="form.errors.project_id" class="text-red-600 text-sm mt-1">{{ form.errors.project_id }}</p>
    </div>

    <div>
      <label class="font-semibold">رقم العمارة</label>
      <input v-model="form.building_number" class="border rounded-xl p-3 w-full" />
      <p v-if="form.errors.building_number" class="text-red-600 text-sm mt-1">{{ form.errors.building_number }}</p>
    </div>

    <div>
      <label class="font-semibold">رقم الشقة</label>
      <input v-model="form.number" class="border rounded-xl p-3 w-full" />
      <p v-if="form.errors.number" class="text-red-600 text-sm mt-1">{{ form.errors.number }}</p>
      
    </div>

    <div>
      <label class="font-semibold">الطابق</label>
      <select v-model="form.floor" class="border rounded-xl p-3 w-full">
        <option value="0">أرضي</option>
        <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
      </select>
      <p v-if="form.errors.floor" class="text-red-600 text-sm mt-1">{{ form.errors.floor }}</p>
    </div>

    <div>
      <label class="font-semibold">عدد الغرف</label>
      <select v-model="form.rooms" class="border rounded-xl p-3 w-full">
        <option value="2">2</option>
        <option value="3">3</option>
      </select>
    </div>

    <div>
      <label class="font-semibold">رقم الشطر</label>
      <input v-model="form.tranche_number" class="border rounded-xl p-3 w-full" />
    </div>

    <div>
      <label class="font-semibold">المساحة (م²)</label>
      <input v-model="form.area" type="number" class="border rounded-xl p-3 w-full" />
      <p v-if="form.errors.area" class="text-red-600 text-sm mt-1">{{ form.errors.area }}</p>
    </div>

    <div>
      <label class="font-semibold">ثمن المتر</label>
      <input v-model="form.price_per_m2" type="number" class="border rounded-xl p-3 w-full" />
      <p v-if="form.errors.price_per_m2" class="text-red-600 text-sm mt-1">{{ form.errors.price_per_m2 }}</p>
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

  <!-- صاحب الشقة -->
  <div>
    <h3 class="font-bold  mb-4">صاحب الشقة</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div>
        <label class="font-semibold">اسم صاحب الشقة</label>
        <input v-model="form.customer_name" class="border rounded-xl p-3 w-full" />
      </div>
      <div>
        <label class="font-semibold">رقم البطاقة</label>
        <input v-model="form.customer_id" class="border rounded-xl p-3 w-full" />
      </div>
      <div>
        <label class="font-semibold">رقم الهاتف</label>
        <input v-model="form.customer_phone" class="border rounded-xl p-3 w-full" />
      </div>
    </div>
  </div>
</div>

<!-- STEP 2 -->
<div v-show="activeStep === 2" class="space-y-4">
  <h2 class="font-bold">موقف السيارة</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>
      <label class="font-semibold">رقم الموقف</label>
      <input v-model="form.parking_number" class="border rounded-xl p-3 w-full" />
    </div>
    <div>
      <label class="font-semibold">ثمن الموقف</label>
      <input v-model="form.parking_price" type="number" class="border rounded-xl p-3 w-full" />
    </div>
  </div>
</div>

<!-- STEP 3 -->
<div v-show="activeStep === 3" class="space-y-4">
  <h2 class="font-bold">Terrasse/Coeur</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>
      <label class="font-semibold">النوع</label>
      <select v-model="form.terrace_type" class="border rounded-xl p-3 w-full">
        <option value="">اختر</option>
        <option value="terrasse">Terrasse</option>
        <option value="coeur">Coeur</option>
      </select>
    </div>
    <div>
      <label class="font-semibold">المساحة</label>
      <input v-model="form.terrace_area" type="number" class="border rounded-xl p-3 w-full" />
    </div>
    <div>
      <label class="font-semibold">القيمة</label>
      <input :value="terraceTotal" disabled class="border rounded-xl p-3 w-full bg-gray-100" />
    </div>
  </div>
</div>

<!-- STEP 4 -->
<div v-show="activeStep === 4" class="space-y-4">
  <h2 class="font-bold">التخفيض</h2>
  <div>
    <label class="font-semibold">   قيمة التخفيض   </label>
    <input v-model="form.discount" type="number" class="border rounded-xl p-3 w-full max-w-sm" />
  </div>
</div>

</div>
</div>

<!-- ================= RIGHT ================= -->
<div class="space-y-6">

<div class="bg-white rounded-2xl shadow p-6">
  <h3 class="font-bold mb-3">صورة الشقة</h3>
  <label class="border-2 border-dashed h-56 flex items-center justify-center cursor-pointer">
    <input type="file" class="hidden" @change="handleImage" />
    <span v-if="!imagePreview">اضغط لإضافة صورة</span>
    <img v-else :src="imagePreview" class="max-h-40" />
  </label>
</div>

<!-- ===== SUMMARY ===== -->
<div class="bg-white rounded-2xl shadow p-6 h-fit">
  <h3 class="font-bold mb-4">ملخص السعر</h3>

  <div class="space-y-2 text-sm">
    <div class="flex justify-between"><span>ثمن الشقة </span><span>{{formatMoney(baseTotal) }}</span></div>
    <div v-if="form.parking_price" class="flex justify-between text-green-700">
      <span> موضع السيارة</span><span>{{ formatMoney(form.parking_price) }}</span>
    </div>
    <div v-if="terraceTotal" class="flex justify-between text-green-700">
      <span> {{ form.terrace_type }}</span><span>{{ formatMoney(terraceTotal) }}</span>
    </div>
    <div v-if="form.discount" class="flex justify-between text-red-600">
      <span> قيمة التخفيض</span><span> - {{ formatMoney(form.discount)}}</span>
    </div>
  </div>

  <div class="border-t mt-4 pt-3 flex justify-between font-bold text-green-700">
    <span>الثمن الاجمالي</span>
    <span>{{ formatMoney(totalPrice )}} درهم</span>
  </div>
</div>

</div>
</div>

<div class="mt-6 bg-white p-6 rounded-2xl shadow max-w-7xl mx-auto">
  <button  type="submit" class="bg-green-700 text-white px-10 py-3 rounded-xl">
    حفظ تعديلات الشقة
  </button>
</div>

</div>
</form>
</AppLayout>
</template>
