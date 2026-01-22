<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
    companies: Array,
    filters: Object
})

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const focusCompany = params.get('focus-company')

  if (!focusCompany) return

  setTimeout(() => {
    const el = document.getElementById(`company-${focusCompany}`)

    if (!el) {
      console.warn('Company element not found')
      return
    }

    el.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
    })

    el.classList.add('ring-4', 'ring-yellow-400')

    setTimeout(() => {
      el.classList.remove('ring-4', 'ring-yellow-400')
    }, 1800)

  }, 400) // ⬅️ مهم
})


// الحالة
const showDeleteModal = ref(false)
const deleting = ref(false)
const companyToDelete = ref(null)

// فتح المودال
function confirmDelete(company) {
  companyToDelete.value = company
  showDeleteModal.value = true
}

// تنفيذ الحذف
function remove() {
  if (!companyToDelete.value) return

  deleting.value = true

  router.delete(`/companies/${companyToDelete.value.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      showDeleteModal.value = false
      companyToDelete.value = null
    },
  })
}

</script>

<template>
<AppLayout>



    <div class="p-6">


<!-- العنوان -->
<div class="mb-4">
  <h1 class="text-3xl font-bold text-green-700">
    الشركات
  </h1>

  <!-- خط فاصل -->
  <div class="mt-7 h-px bg-gray-200"></div>
</div>

        <!-- شبكة البطاقات -->
        <div  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6  ">

            <div 
                v-for="company in companies" 
                :key="company.id"
                :id="`company-${company.id}`"
                @click="router.visit(`/companies/${company.id}`)"
                class="bg-white rounded-3xl p-6 shadow-md hover:shadow-xl 
                       transition duration-300 text-center flex flex-col items-center
                       min-h-[160px] cursor-pointer relative"
            >

                <!-- طبقة تمنع الضغط على الأزرار من فتح البطاقة -->
                <div 
                    class="absolute inset-0"
                    @click.stop
                ></div>

                <!--اسم الشركة-->
                <h2 class="text-2xl font-extrabold text-gray-800 mb-2 pointer-events-none">
                    {{ company.name }}
                </h2>

                <!-- العدادات -->
                <div class="flex justify-center gap-10 mb-5 pointer-events-none">

                    <div>
                        <p class="text-lg font-bold text-gray-700">
                            {{ company.projects_count ?? 0 }}
                        </p>
                        <p class="text-gray-500 text-xs">إجمالي المشاريع</p>
                    </div>

                    <div>
                        <p class="text-lg font-bold text-gray-700">
                            {{ company.buildings_projects_count ?? 0 }}
                        </p>
                        <p class="text-gray-500 text-xs">عمارات سكنية</p>
                    </div>

                    <div>
                        <p class="text-lg font-bold text-gray-700">
                            {{ company.land_projects_count ?? 0 }}
                        </p>
                        <p class="text-gray-500 text-xs">تجزئات أرضية</p>
                    </div>

                </div>

                <!-- الأزرار -->
                <div class="flex gap-2 mt-auto z-10 pointer-events-auto">

<!-- تعديل -->
<a
    :href="`/companies/${company.id}/edit`"
    class="px-4 py-1.5 rounded-xl border border-green-700
           text-green-600 bg-white text-sm hover:bg-green-50 font-bold transition
           flex items-center gap-1"
    @click.stop
>
    <!-- أيقونة التعديل -->
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M11 5h2M12 3v2m7.071 1.929a2 2 0 010 2.828l-9.9 9.9a2 2 0 01-.828.486l-3.243 1.081 1.081-3.243a2 2 0 01.486-.828l9.9-9.9a2 2 0 012.828 0z"/>
    </svg>

    تعديل
</a>


<!-- حذف -->
<button 
    @click="confirmDelete(company)"
    class="px-4 py-1.5 rounded-xl border border-green-700 
           text-green-600 bg-white text-sm hover:bg-green-50 font-bold transition
           flex items-center gap-1"
>
    <!-- أيقونة الحذف -->
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 7V4h6v3"/>
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 7h18"/>
    </svg>

    حذف
</button>


                </div>

            </div>

        </div>

    </div>

    <!-- Delete Company Modal -->
<div
  v-if="showDeleteModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف الشركة
    </h2>

    <!-- الجملة -->
    <p class="text-gray-700 text-lg leading-relaxed mb-6">
      هل تريد حذف الشركة
      <span class="font-bold text-gray-900">
        {{ companyToDelete?.name }}
      </span>
      ؟
    </p>

    <!-- تحذير -->
    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      ⚠️ سيتم حذف جميع المشاريع المرتبطة بهذه الشركة  
      <br>
      هذا الإجراء نهائي ولا يمكن التراجع عنه
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
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف الشركة' }}
      </button>

    </div>

  </div>
</div>


</AppLayout>
</template>
