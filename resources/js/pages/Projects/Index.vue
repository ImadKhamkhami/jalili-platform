<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    projects: Array,
    filters: Object,
})

/* ======================
   Tabs
====================== */
const projectTabs = [
    { key: 'building', label: 'العمارات' },
    { key: 'lot', label: 'التجزئات' },
]

const activeTab = ref(
    props.filters?.type === 'lot' ? 'lot' : 'building'
)

function openTab(type) {
    activeTab.value = type
}

/* ======================
   Focus بعد الإضافة / التعديل
====================== */
onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const focus = params.get('focus')
    const type  = params.get('type')

    if (type === 'lot' || type === 'building') {
        activeTab.value = type
    }

    if (!focus) return

    setTimeout(() => {
        const el = document.getElementById(`project-${focus}`)
        if (!el) return

        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        el.classList.add('ring-4', 'ring-yellow-400')

        setTimeout(() => {
            el.classList.remove('ring-4', 'ring-yellow-400')
        }, 1800)
    }, 400)
})

/* ======================
   فلترة المشاريع حسب التبويب
====================== */
const filteredProjects = computed(() => {
    return props.projects.filter(p => p.type === activeTab.value)
})

/* ======================
   حذف مشروع
====================== */
const showDeleteModal = ref(false)
const deleting = ref(false)
const projectToDelete = ref(null)

function confirmDelete(project) {
    projectToDelete.value = project
    showDeleteModal.value = true
}

function remove() {
    if (!projectToDelete.value) return

    deleting.value = true

    router.delete(`/projects/${projectToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false
            showDeleteModal.value = false
            projectToDelete.value = null
        },
    })
}


function printProjectStatement(projectId) {
  if (!projectId) {
    alert('projectId غير موجود')
    return
  }

  window.open(
    `/projects/${projectId}/statement`,
    '_blank'
  )
}

</script>

<template>
<AppLayout>

<div class="p-6">

    <!-- العنوان + زر الإضافة -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-green-700">المشاريع</h1>
    </div>

    <!-- Tabs -->
    <div class="flex gap-6 border-b pb-2 mb-6 font-bold">
        <button
            v-for="tab in projectTabs"
            :key="tab.key"
            @click="openTab(tab.key)"
            class="pb-2 transition"
            :class="activeTab === tab.key
                ? 'text-green-700 border-b-2 border-green-700'
                : 'text-gray-500 hover:text-green-700'"
        >
            {{ tab.label }}
        </button>
    </div>

    <!-- شبكة البطاقات -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <div
            v-for="project in filteredProjects"
            :key="project.id"
            :id="`project-${project.id}`"
            class="relative bg-white rounded-3xl p-6 shadow-md hover:shadow-xl
                   transition text-center flex flex-col items-center cursor-pointer"
            @click="router.visit(
                project.type === 'building'
                    ? `/projects/${project.id}/apartments`
                    : `/projects/${project.id}/lands`
            )"
        >

            <!-- الشارات العلوية -->
            <div class="absolute top-4 left-4 flex gap-2">

                <template v-if="project.type === 'building'">
                <span class="px-3 py-1 rounded-full text-xs font-bold
                                 text-black-700 border border-black">
                        {{ project.apartments_count ?? 0 }} شقة
                </span>
               <span class="px-3 py-1 rounded-full text-xs font-bold
                 text-black-700 border border-black">
                 {{ project.shops_count ?? 0 }} محل
               </span>

                </template>
                <template v-else>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                 text-black-700 border border-black">
                        {{ project.land_plots_count ?? 0 }} قطعة
                    </span>
                </template>

            </div>

            <!-- اسم المشروع -->
            <h2 class="text-2xl font-extrabold text-gray-800 mb-2 mt-6">
                {{ project.name }}
            </h2>

            <!-- رقم الرسم العقاري -->
            <div v-if="project.titre_foncier" class="text-gray-600 mb-2">
                <span class="font-semibold">TF:</span>
                {{ project.titre_foncier }}
            </div>

            <!-- الشركة -->
            <p class="text-xl text-gray-800 mb-4 font-bold">
               شركة : <strong>{{ project.company.name }}</strong>
            </p>

            <!-- الأزرار -->
            <div class="flex gap-2 mt-auto" @click.stop>
            <button
                @click="printProjectStatement(project.id)"
                class="px-4 py-1.5 rounded-xl
                       border-2 border-yellow-600
                       text-yellow-700 bg-white text-sm font-bold
                       hover:bg-yellow-50 transition"
            >
                طباعة بيان الدفوعات
            </button>

<a
    :href="`/projects/${project.id}/edit`"
    class="px-4 py-1.5 rounded-xl border border-green-700
           text-green-600 bg-white text-sm hover:bg-green-50 font-bold transition
           flex items-center gap-1"
>
    <!-- أيقونة التعديل -->
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.862 3.487a2.121 2.121 0 013 3L7.5 18.848l-4 1 1-4 12.362-12.361z"/>
    </svg>

    تعديل
</a>


<button
    @click.stop="confirmDelete(project)"
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

<!-- Delete Project Modal -->
<div
  v-if="showDeleteModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
      <span class="text-3xl text-red-600">⚠️</span>
    </div>

    <h2 class="text-2xl font-bold text-red-600 mb-4">
      تأكيد حذف المشروع
    </h2>

    <p class="text-gray-700 text-lg mb-6">
      هل تريد حذف المشروع
      <span class="font-bold text-gray-900">
        {{ projectToDelete?.name }}
      </span>
      ؟
    </p>

    <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-3 mb-6">
      ⚠️ سيتم حذف جميع البيانات المرتبطة بهذا المشروع  
      <br>
      هذا الإجراء نهائي ولا يمكن التراجع عنه
    </div>

    <div class="flex justify-center gap-4">
      <button
        @click="showDeleteModal = false"
        class="px-8 py-3 rounded-xl border border-gray-300 text-gray-700"
      >
        إلغاء
      </button>

      <button
        @click="remove"
        class="px-8 py-3 rounded-xl bg-red-600 text-white"
        :disabled="deleting"
      >
        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف المشروع' }}
      </button>
    </div>

  </div>
</div>

</AppLayout>
</template>