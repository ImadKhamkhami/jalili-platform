<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, router } from '@inertiajs/vue3'

/* ===================== PROPS ===================== */
const props = defineProps({
  transfer: Object,
  unit: Object,
  project: Object,
  from_customer: Object,
  to_customer: Object,
})

/* ===================== FORM ===================== */
const form = useForm({
  to_name: props.to_customer?.name ?? '',
  to_national_id: props.to_customer?.national_id ?? '',
  to_phone: props.to_customer?.phone ?? '',

  transfer_date: props.transfer?.transfer_date ?? '',
  notes: props.transfer?.notes ?? '',
})

function submit() {
  form.put(`/transfers/${props.transfer.id}`, {
    preserveScroll: true,
  })
}

function goBack() {
  router.visit('/transfers')
}
</script>

<template>
<AppLayout title="تعديل تنازل">

  <!-- ================= FORM ================= -->
  <div
    v-if="transfer && from_customer"
    class="p-8 max-w-3xl mx-auto"
  >
    <!-- العنوان -->
    <div class="mb-10 text-center">
      <h1 class="text-3xl font-bold text-green-700">
        تعديل تنازل
      </h1>
      <p class="text-gray-500 mt-2 font-bold">
        {{ unit.label }} – {{ project.name }}
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow p-8 space-y-8">

      <!-- المتنازل + رقم -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="label">المتنازل</label>
          <input
            :value="from_customer.name"
            disabled
            class="input bg-gray-100"
          />
        </div>

        <div>
          <label class="label">رقم التنازل</label>
          <input
            :value="transfer.transfer_number"
            disabled
            class="input bg-gray-100 text-center font-bold"
          />
        </div>
      </div>

      <!-- بيانات المستفيد -->
      <div class="border-t pt-8">
        <h2 class="text-lg font-bold text-gray-700 mb-4">
          بيانات  المستفيد
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input
            v-model="form.to_name"
            placeholder="الاسم الكامل"
            class="input"
          />
          <input
            v-model="form.to_national_id"
            placeholder="رقم البطاقة"
            class="input"
          />
          <input
            v-model="form.to_phone"
            placeholder="الهاتف"
            class="input"
          />
        </div>
      </div>

      <!-- التاريخ -->
      <div class="border-t pt-8">
        <label class="label">تاريخ التنازل</label>
        <input
          type="date"
          v-model="form.transfer_date"
          class="input"
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
          :disabled="form.processing"
          class="px-10 py-3 rounded-xl
                 bg-green-600 text-white font-bold
                 hover:bg-green-700 transition"
        >
          تحديث التنازل
        </button>

        <button
          @click="goBack"
          class="px-10 py-3 rounded-xl border
                 hover:bg-gray-100 transition"
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
