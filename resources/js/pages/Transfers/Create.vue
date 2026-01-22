<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

/* ===================== PROPS ===================== */
const props = defineProps({
  context: String,
  unit: Object,
  project: Object,
  from_customer: {
    type: Object,
    default: null,
  },
  transfer_number: Number,
  transfer_error: {
    type: String,
    default: null,
  },
})

/* ===================== MODAL ===================== */
const showErrorModal = ref(false)

onMounted(() => {
  if (!props.from_customer && props.transfer_error) {
    showErrorModal.value = true
  }
})

/* ===================== FORM (✔️ صحيح) ===================== */
const form = useForm({
  context: props.context,
  unit_id: props.unit?.id ?? null,
  from_customer_id: props.from_customer?.id ?? null,

  to_name: '',
  to_national_id: '',
  to_phone: '',

  transfer_date: '',
  notes: '',
})

function submit() {
  if (!props.from_customer) return

  form.post('/transfers', {
    preserveScroll: true,
  })
}

function goBack() {
  if (props.context === 'apartment') {
    router.visit(`/apartments/${props.unit.id}`)
  } else if (props.context === 'shop') {
    router.visit(`/shops/${props.unit.id}`)
  } else if (props.context === 'land') {
    router.visit(`/lands/${props.unit.id}`)
  } else {
    router.visit('/')
  }
}
</script>


<template>
<AppLayout title="تسجيل تنازل">

  <!-- ================= مودال الخطأ ================= -->
  <div
    v-if="showErrorModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm
           flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-3xl shadow-2xl
                w-full max-w-lg p-8 text-center">

      <div class="mx-auto mb-4 w-14 h-14
                  flex items-center justify-center
                  rounded-full bg-red-100">
        <span class="text-3xl text-red-600">⚠️</span>
      </div>

      <h2 class="text-2xl font-bold text-red-600 mb-4">
        لا يمكن تسجيل التنازل
      </h2>

      <p class="text-gray-700 text-lg leading-relaxed mb-6">
        {{ transfer_error }}
      </p>

      <button
        @click="goBack"
        class="px-10 py-3 rounded-xl
               bg-gray-800 text-white
               hover:bg-black transition"
      >
        رجوع
      </button>
    </div>
  </div>

  <!-- ================= FORM ================= -->
  <div
    v-if="from_customer"
    class="p-8 max-w-3xl mx-auto"
  >
    <!-- العنوان -->
    <div class="mb-10 text-center">
      <h1 class="text-3xl font-bold text-green-700">
        تسجيل تنازل
      </h1>
      <p class="text-gray-500 mt-2 font-bold">
        {{ unit.label }} – {{ project.name }}
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow p-8 space-y-8">

      <!-- المتنازل + رقم -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-bold text-gray-500 mb-1">
            المتنازل
          </label>
          <input
            :value="from_customer.name"
            disabled
            class="w-full bg-gray-100 border border-gray-300
                   rounded-xl px-4 py-2 text-gray-700"
          />
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-500 mb-1">
            رقم التنازل
          </label>
          <input
            :value="transfer_number"
            disabled
            class="w-full bg-gray-100 border border-gray-300
                   rounded-xl px-4 py-2 text-center font-bold"
          />
        </div>
      </div>

      <!-- بيانات المستفيد -->
      <div class="border-t pt-8">
        <h2 class="text-lg font-bold text-gray-700 mb-4">
          بيانات المستفيد 
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input v-model="form.to_name" placeholder="الاسم الكامل"
            class="input" />
          <input v-model="form.to_national_id" placeholder="رقم البطاقة"
            class="input" />
          <input v-model="form.to_phone" placeholder="الهاتف"
            class="input" />
        </div>
      </div>

      <!-- التاريخ -->
      <div class="border-t pt-8">
        <label class="label">تاريخ التنازل</label>
        <input type="date" v-model="form.transfer_date" class="input" />
      </div>

      <!-- ملاحظات -->
      <div class="border-t pt-8">
        <label class="label">ملاحظات</label>
        <textarea rows="3" v-model="form.notes" class="input"></textarea>
      </div>

      <!-- الأزرار -->
      <div class="flex justify-center gap-6 pt-8">
        <button
          @click="submit"
          class="px-10 py-3 rounded-xl bg-green-600
                 text-white font-bold hover:bg-green-700"
        >
          حفظ التنازل
        </button>

        <button
          @click="goBack"
          class="px-10 py-3 rounded-xl border"
        >
          إلغاء
        </button>
      </div>
    </div>
  </div>
</AppLayout>
</template>

<style scoped>
.input {
  width: 100%;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  padding: 0.5rem 1rem;
}
.label {
  font-size: 0.875rem;
  font-weight: bold;
  color: #6b7280;
  margin-bottom: 0.25rem;
  display: block;
}
</style>
