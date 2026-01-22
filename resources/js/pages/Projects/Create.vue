<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  companies: Array,
  types: Array,
})

const form = useForm({
  name: '',
  company_id: '',
  type: '',
  titre_foncier: '',
})

function submit() {
  form.post('/projects')
}
</script>

<template>
  <AppLayout>

    <div class="flex justify-center py-16">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-xl px-10 py-12">

        <!-- العنوان -->
        <h1 class="text-2xl font-bold text-center text-green-700 mb-10">
          إضافة مشروع جديد
        </h1>

        <!-- اسم المشروع -->
        <div class="mb-6">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            اسم المشروع
          </label>

          <input
            v-model="form.name"
            type="text"
            class="w-full rounded-xl
                   border border-gray-300
                   bg-gray-50
                   px-4 py-3
                   text-gray-800
                   focus:bg-white
                   focus:border-green-600
                   focus:ring-2 focus:ring-green-200
                   transition"
          />

          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
            {{ form.errors.name }}
          </div>
        </div>
        <!-- رقم الرسم العقاري -->
<div class="mb-6">
  <label class="block mb-2 text-sm font-semibold text-green-800">
    رقم الرسم العقاري (Titre foncier)
  </label>

  <input
    v-model="form.titre_foncier"
    type="text"
    placeholder="مثال: TF 4587/2020"
    class="w-full rounded-xl
           border border-gray-300
           bg-gray-50
           px-4 py-3
           text-gray-800
           focus:bg-white
           focus:border-green-600
           focus:ring-2 focus:ring-green-200
           transition"
  />

  <div v-if="form.errors.titre_foncier"
       class="mt-1 text-sm text-red-600">
    {{ form.errors.titre_foncier }}
  </div>
</div>


        <!-- الشركة -->
        <div class="mb-6">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            الشركة
          </label>

          <select
            v-model="form.company_id"
            class="w-full rounded-xl
                   border border-gray-300
                   bg-gray-50
                   px-4 py-3
                   text-gray-800
                   focus:bg-white
                   focus:border-green-600
                   focus:ring-2 focus:ring-green-200
                   transition"
          >
            <option value="">اختر الشركة</option>
            <option
              v-for="company in companies"
              :key="company.id"
              :value="company.id"
            >
              {{ company.name }}
            </option>
          </select>

          <div v-if="form.errors.company_id" class="mt-1 text-sm text-red-600">
            {{ form.errors.company_id }}
          </div>
        </div>

        <!-- نوع المشروع -->
        <div class="mb-8">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            نوع المشروع
          </label>

          <select
            v-model="form.type"
            class="w-full rounded-xl
                   border border-gray-300
                   bg-gray-50
                   px-4 py-3
                   text-gray-800
                   focus:bg-white
                   focus:border-green-600
                   focus:ring-2 focus:ring-green-200
                   transition"
          >
            <option value="">اختر النوع</option>
                <option value="building">عمارة سكنية</option>
                <option value="lot">تجزئة أرضية</option>
          </select>

          <div v-if="form.errors.type" class="mt-1 text-sm text-red-600">
            {{ form.errors.type }}
          </div>
        </div>

        <!-- زر الحفظ -->
        <button
          @click="submit"
          :disabled="form.processing"
          class="w-full bg-green-600 hover:bg-green-700
                 text-white font-semibold py-3 rounded-xl
                 transition disabled:opacity-50"
        >
          حفظ المشروع
        </button>

      </div>
    </div>

  </AppLayout>
</template>
