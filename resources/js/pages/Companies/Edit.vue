<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  company: Object,
})

const form = useForm({
  name: props.company.name || '',
})

function submit() {
  form.put(`/companies/${props.company.id}`)
}
</script>

<template>
  <AppLayout>

    <div class="flex justify-center py-16 mt-20">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-xl px-10 py-12">

        <!-- العنوان -->
        <h1 class="text-2xl font-bold text-center text-green-700 mb-10">
          تعديل الشركة
        </h1>

        <!-- اسم الشركة -->
        <div class="mb-8">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            اسم الشركة
          </label>

          <input
            v-model="form.name"
            type="text"
            placeholder="أدخل اسم الشركة"
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

        <!-- الأزرار -->
        <div class="flex gap-4 justify-end">
          <button
            @click="submit"
            :disabled="form.processing"
            class="flex-1 bg-green-600 hover:bg-green-700
                   text-white font-semibold py-3 rounded-xl
                   transition disabled:opacity-50"
          >
            حفظ التعديلات
          </button>
        </div>

      </div>
    </div>

  </AppLayout>
</template>
