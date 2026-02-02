<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, router } from '@inertiajs/vue3'

/* ===================== PROPS ===================== */
const props = defineProps({
  context: String,        // land | apartment | shop
  unit: Object,           // القطعة / الشقة / المحل
  project: Object,        // المشروع
})

/* ===================== FORM ===================== */
const form = useForm({
  context: props.context,
  unit_id: props.unit?.id ?? null,

  amount: '',
  commission_date: '',
  broker_name: '',
  notes: '',
})

function submit() {
  form.post('/commissions', {
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
<AppLayout title="إضافة سمسرة">

  <div class="p-8 max-w-3xl mx-auto">

    <!-- العنوان -->
    <div class="mb-10 text-center">
      <h1 class="text-3xl font-bold text-green-700">
        إضافة سمسرة
      </h1>
      <p class="text-gray-500 mt-2 font-bold">
        {{ unit.label }} – {{ project.name }}
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow p-8 space-y-8">

      <!-- مبلغ + تاريخ -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="label">مبلغ السمسرة</label>
          <input
            v-model="form.amount"
            type="number"
            class="input text-center font-bold"
          />
        </div>

        <div>
          <label class="label">تاريخ السمسرة</label>
          <input
            v-model="form.commission_date"
            type="date"
            class="input"
          />
        </div>
      </div>

      <!-- اسم السمسار -->
      <div class="border-t pt-8">
        <label class="label">اسم السمسار (اختياري)</label>
        <input
          v-model="form.broker_name"
          class="input"
          placeholder="اسم السمسار"
        />
      </div>

      <!-- ملاحظات -->
      <div class="border-t pt-8">
        <label class="label">ملاحظات</label>
        <textarea
          rows="3"
          v-model="form.notes"
          class="input"
        ></textarea>
      </div>

      <!-- الأزرار -->
      <div class="flex justify-center gap-6 pt-8">
        <button
          @click="submit"
          class="px-10 py-3 rounded-xl bg-green-600
                 text-white font-bold hover:bg-green-700"
        >
          حفظ السمسرة
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
