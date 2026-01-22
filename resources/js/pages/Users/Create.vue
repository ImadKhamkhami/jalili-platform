<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  roles: Array,
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: '',
})

function submit() {
  form.post('/users')
}
</script>

<template>
  <AppLayout>

    <div class="flex justify-center py-16">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-xl px-10 py-12">

        <!-- العنوان -->
        <h1 class="text-2xl font-bold text-center text-green-700 mb-10">
          إضافة مستخدم جديد
        </h1>

        <!-- الاسم -->
        <div class="mb-6">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            الاسم
          </label>

          <input
            v-model="form.name"
            type="text"
            autocomplete="off"
            placeholder="اسم المستخدم"
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

        <!-- البريد الإلكتروني -->
        <div class="mb-6">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            البريد الإلكتروني
          </label>

          <input
            v-model="form.email"
            type="email"
            autocomplete="off"
            placeholder="example@email.com"
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

          <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- كلمة المرور -->
        <div class="mb-6">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            كلمة المرور
          </label>

          <input
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            placeholder="********"
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

          <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- الدور -->
        <div class="mb-8">
          <label class="block mb-2 text-sm font-semibold text-green-800">
            الدور
          </label>

          <select
            v-model="form.role"
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
            <option value="">اختر الدور</option>
            <option
              v-for="role in roles"
              :key="role.id"
              :value="role.name"
            >
              {{ role.name }}
            </option>
          </select>

          <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
            {{ form.errors.role }}
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
          حفظ المستخدم
        </button>

      </div>
    </div>

  </AppLayout>
</template>
