<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { ref, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";


function printLandPlan(projectId) {
    window.open(`/projects/${projectId}/lands/plan/pdf`, '_blank')
}

const progressStyle = (land) => {
  if (!land.is_sold) {
    return {
      width: '0%',
      backgroundColor: '#d1d5db', // رمادي
    }
  }

  return {
    width: `${land.payment_percentage}%`,
    backgroundColor: '#16a34a', // أخضر جليلي
  }
}


function paymentPercent(land) {
  const total = Number(land.total_price ?? 0)
  const paid  = Number(land.paid ?? 0)

  if (total <= 0) return 0

  return Math.min(
    Math.round((paid / total) * 100),
    100
  )
}

function formatMoney(value) {
  const number = Number(value ?? 0)

  return number.toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}



/* ================= PROPS ================= */
const props = defineProps({
    lands: Array,
    projects: Array,
    current_project: Object, // يأتي من Controller
});

/* ================= PROJECT TABS ================= */
const projectList = computed(() => props.projects ?? []);
const hasProjects = computed(() => projectList.value.length > 0);

function openProject(id) {
    router.visit(`/projects/${id}/lands`);
}

/* ================= STATUS COLOR (مثل الشقق) ================= */
function statusColor(status) {
    switch (status) {
        case "متاحة": return "#2E7D32";
        case "محجوزة": return "#D08700";
        case "مباعة": return "#E53935";
        default: return "#666";
    }
}

/* ================= FOCUS AFTER ADD ================= */
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const focusLand = params.get("focus-land");

    if (!focusLand) return;

    setTimeout(() => {
        const el = document.getElementById(`land-${focusLand}`);
        if (el) {
            el.scrollIntoView({ behavior: "smooth", block: "center" });
            el.classList.add("ring-4", "ring-yellow-400");
            setTimeout(() => {
                el.classList.remove("ring-4", "ring-yellow-400");
            }, 1500);
        }
    }, 350);
});
</script>

<template>
<AppLayout title="القطع الأرضية">
    <div class="p-6">

        <!-- ================= HEADER ================= -->
        <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-green-700">
        القطع الأرضية
    </h1>

    <div class="flex items-center gap-3">
        <!-- 🖨 زر طباعة مخطط القطع (نفس الشقق) -->
<button
  @click="printLandPlan(current_project.id)"
  title="طباعة بيان الدفوعات"
  class="group h-10 w-10 flex items-center justify-center
         bg-yellow-50 text-yellow-600
         border border-yellow-500 rounded-xl
         hover:bg-yellow-100
         active:scale-95 transition-all shadow-sm"
>
  <svg xmlns="http://www.w3.org/2000/svg"
       class="w-5 h-5 group-hover:scale-110 transition"
       fill="none"
       viewBox="0 0 24 24"
       stroke="currentColor"
       stroke-width="2">
    <path stroke-linecap="round"
          stroke-linejoin="round"
          d="M6 9V2h12v7
             M6 18H4a2 2 0 0 1-2-2v-5
             a2 2 0 0 1 2-2h16
             a2 2 0 0 1 2 2v5
             a2 2 0 0 1-2 2h-2
             M6 14h12v8H6z" />
  </svg>
</button>
    </div>
        </div>
        <!-- ================= PROJECT TABS (نفس الشقق) ================= -->
        <div v-if="hasProjects" class="flex gap-6 border-b pb-2 mb-6 font-bold">
            <button
                v-for="p in projectList"
                :key="p.id"
                @click="openProject(p.id)"
                class="pb-2 transition"
                :class="current_project?.id === p.id
                    ? 'text-green-700 border-b-2 border-green-700'
                    : 'text-gray-500 hover:text-green-700'"
            >
                {{ p.name }}
            </button>
        </div>

        <!-- ================= EMPTY ================= -->
        <div
            v-if="!lands.length"
            class="text-center text-gray-500 py-12 text-lg"
        >
            لا توجد قطع أرضية في هذا المشروع
        </div>

        <!-- ================= GRID (5 في السطر) ================= -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">

        <!-- ================= CARD ================= -->
        <div
                v-for="land in lands"
                :key="land.id"
                :id="`land-${land.id}`"
                @click="router.visit(`/lands/${land.id}`)"
               class="relative bg-white border shadow rounded-xl pt-1 px-6 pb-5 text-center
               hover:bg-green-50 hover:shadow-md hover:scale-[1.02]
               transition cursor-pointer text-sm" >

                <!-- 🟢🟡🔴 شارة الحالة (مطابقة للشقق) -->
                <div
                    class="absolute top-2 left-2 flex items-center gap-1 bg-white border
                           px-3 py-1 rounded-full text-xs font-bold shadow-sm"
                    :style="{ borderColor: statusColor(land.status), color: statusColor(land.status) }"
                >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                    {{ land.status }}
                </div>



                <!-- ================= CONTENT ================= -->
                <h3 class="text-xl font-bold text-gray-900 mt-8">
                    القطعة  {{ land.land_number }}
                </h3>
                <p class="text-sm text-gray-700 mt-2 flex justify-center items-center gap-3">
  <!-- المساحة -->
  <span>
    <span class="font-bold">{{ land.area }}</span> م²
  </span>

  <!-- خط عمودي -->
  <span class="h-4 w-px bg-gray-300"></span>

  <!-- نوع الطريق -->
  <span class="text-gray-600 font-bold">
     طريق {{ land.road_type }} 
  </span>
    <!-- خط عمودي -->
  <span class="h-4 w-px bg-gray-300"></span>
    <span class="text-gray-600 font-bold">
    {{ land.view_type === '1-FACADE' ? '1F' : '2F' }}
  </span>
</p>


                <p class="text-sm font-bold text-gray-700 mt-2">
                  صاحب القطعة :
                  <span class="font-bold">
                        {{ land.customer_name }}
                  </span>
                </p>
<!-- Progress -->
<div class="mt-1"> <!-- كانت mt-4 -->

  <!-- العنوان + النسبة -->
  <div class="flex justify-between items-center text-[13px] mb-1 text-gray-500">
    <span>نسبة الدفوعات</span>
    <span class="font-bold text-green-700 text-lg">
      {{ land.payment_percentage }}%
    </span>
  </div>

  <!-- الشريط -->
  <div
    class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden"
    style="direction:ltr"
  >
    <div
      class="h-full  rounded-full transition-all duration-500 ease-out"
      :style="{
        width: land.payment_percentage + '%',
        backgroundColor: '#16a34a'
      }"
    ></div>
  </div>

</div>


        </div>
        <!-- ================= END CARD ================= -->

        </div>
    </div>
</AppLayout>
</template>
