<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { Eye, Pencil } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  users: any[]
}>()

type User = {
  id: number
  name: string
}

const showDeleteModal = ref(false)
const deleting = ref(false)
const userToDelete = ref<User | null>(null)

function confirmDelete(user: User) {
  userToDelete.value = user
  showDeleteModal.value = true
}

function remove() {
  if (!userToDelete.value) return

  deleting.value = true

  router.delete(`/users/${userToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
      userToDelete.value = null
    },
  })
}

</script>

<template>
  <AppLayout title="المستخدمون">
    <div class="p-6 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-green-700">
          المستخدمون
        </h1>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-right">

            <!-- Head -->
            <thead class="bg-gray-50 text-green-700 sticky top-0 z-10">
              <tr>
                <th class="px-4 py-3 font-semibold">الاسم</th>
                <th class="px-4 py-3 font-semibold">البريد</th>
                <th class="px-4 py-3 font-semibold">الدور</th>
                <th class="px-4 py-3 font-semibold text-center">الإجراءات</th>
              </tr>
            </thead>

            <!-- Body -->
            <tbody>
              <tr
                v-for="user in users"
                :key="user.id"
                class="border-t hover:bg-gray-50 transition"
              >
                <!-- Name -->
                <td class="px-4 py-3 font-medium text-gray-800">
                  {{ user.name }}
                </td>

                <!-- Email -->
                <td class="px-4 py-3 text-gray-600">
                  {{ user.email }}
                </td>

                <!-- Roles -->
                <td class="px-4 py-3">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex items-center px-2 py-0.5 mr-1
                           rounded-full text-xs font-medium
                           bg-green-100 text-green-700"
                  >
                    {{ role.name }}
                  </span>

                  <span
                    v-if="!user.roles || user.roles.length === 0"
                    class="text-gray-400 text-xs"
                  >
                    —
                  </span>
                </td>

          <!-- Actions -->
         <td class="px-4 py-3">
  <div class="flex items-center justify-center gap-2">

    <!-- تعديل المستخدم -->
    <Link
      :href="`/users/${user.id}/edit`"
      class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition"
      title="تعديل"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor"
           stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.862 3.487a2.25 2.25 0 013.182 3.182
                 L8.25 18.463 3.75 20.25l1.787-4.5
                 L16.862 3.487z" />
      </svg>
    </Link>

    <!-- حذف المستخدم -->
  <button
  @click="confirmDelete(user)"
  class="p-2 text-red-600 hover:bg-red-50 rounded-full transition"
  title="حذف"
>
  <svg xmlns="http://www.w3.org/2000/svg"
       class="w-5 h-5"
       fill="none"
       viewBox="0 0 24 24"
       stroke="currentColor"
       stroke-width="1.8">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862
             a2 2 0 01-1.995-1.858L5 7
             m5 4v6m4-6v6
             M9 7h6m2 0H7" />
  </svg>
</button>


  </div>
          </td>

              </tr>

              <!-- Empty -->
              <tr v-if="users.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                  لا يوجد مستخدمون
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>

    </div>
    <!-- Delete User Modal -->
<div
  v-if="showDeleteModal && userToDelete"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف المستخدم
    </h2>

    <!-- الجملة -->
    <p class="text-gray-700 text-lg leading-relaxed mb-6">
      هل تريد حذف المستخدم
      <span class="font-bold text-gray-900">
        {{ userToDelete.name }}
      </span>
      ؟
    </p>

    <!-- تحذير -->
    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      ⚠️ هذا الإجراء نهائي ولا يمكن التراجع عنه
    </div>

    <!-- الأزرار -->
    <div class="flex justify-center gap-4">
      <button
        @click="showDeleteModal = false"
        class="px-8 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
        :disabled="deleting"
      >
        إلغاء
      </button>

      <button
        @click="remove"
        class="px-8 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 transition disabled:opacity-50"
        :disabled="deleting"
      >
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف المستخدم' }}
      </button>
    </div>

  </div>
</div>


  </AppLayout>
</template>

