<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'


const props = defineProps({
    current_project: Object,
    projectId: Number,
    projects: { type: Array, default: () => [] },
    apartments: { type: Array, default: () => [] },
    shops: { type: Array, default: () => [] },
})



const page = usePage()
const project = computed(() => page.props.project)
console.log('PROJECT:', page.props.project)

const projectIdFromUrl = computed(() => {
  const match = window.location.pathname.match(/projects\/(\d+)/)
  return match ? match[1] : null
})


const showPrintModal = ref(false)

function printProjectStatement() {
  if (!projectIdFromUrl.value) {
    alert('projectId غير موجود في الرابط')
    return
  }

  window.open(
    `/projects/${projectIdFromUrl.value}/statement`,
    '_blank'
  )
}


function printBuildingStatement() {
  window.open('/print/building/statement', '_blank')
  showPrintModal.value = false
}

/* ================= طباعة المخطط ================= */

function printPlan() {
    if (!selectedGroup.value) return

    const { building_id, tranche } = selectedGroup.value
    window.open(
        `/buildings/${building_id}/plan/pdf?tranche=${tranche ?? ''}`,
        '_blank'
    )
}

function openPrintModal() {
  showPrintModal.value = true
}


/* ================= المشاريع ================= */
const projectList = computed(() => props.projects ?? [])
const hasProjects = computed(() => projectList.value.length > 0)

const selectedProject = ref(
    props.current_project?.id ??
    projectList.value[0]?.id ??
    null
)

function openProject(id) {
    router.visit(`/projects/${id}/apartments`)
}

/* ================= COLORS ================= */
function statusColor(status) {
    return status === 'مباعة' ? '#E53935'
         : status === 'متاحة' ? '#2E7D32'
         : status === 'محجوزة' ? '#D08700'
         : '#666'
}

function shopStatusColor(status) {
    return status === 'مباع' ? '#E53935'
         : status === 'متاح' ? '#2E7D32'
         : status === 'محجوز' ? '#D08700'
         : '#666'
}

/* ================= 🔥 التجميع الموحد ================= */
const buildingGroups = computed(() => {
    const groups = {}

    // ---------- الشقق ----------
    props.apartments
        .filter(ap => ap.building?.project_id === selectedProject.value)
        .forEach(ap => {
            const b = ap.building
            const tranche = ap.tranche_number ?? null

            const key = tranche
                ? `عمارة ${b.name} - شطر ${tranche}`
                : `عمارة ${b.name}`

            if (!groups[key]) {
                groups[key] = {
                    label: key,
                    building_id: b.id,
                    building_name: b.name,
                    tranche,
                    apartments: [],
                    shops: [],
                }
            }

            groups[key].apartments.push(ap)
        })

    // ---------- المحلات ----------
    props.shops
        .filter(shop => shop.building?.project_id === selectedProject.value)
        .forEach(shop => {
            const b = shop.building
            const tranche = shop.tranche_number ?? null

            const key = tranche
                ? `عمارة ${b.name} - شطر ${tranche}`
                : `عمارة ${b.name}`

            if (!groups[key]) {
                groups[key] = {
                    label: key,
                    building_id: b.id,
                    building_name: b.name,
                    tranche,
                    apartments: [],
                    shops: [],
                }
            }

            groups[key].shops.push(shop)
        })

    return Object.values(groups)
        .sort((a, b) => {
            // ترتيب العمارة
            const buildingCompare =
                Number(a.building_name) - Number(b.building_name)

            if (buildingCompare !== 0) return buildingCompare

            // ترتيب الشطر
            return Number(a.tranche || 0) - Number(b.tranche || 0)
        })
})


const selectedGroup = ref(null)

/* ================= onMounted (FOCUS الموحد) ================= */
onMounted(async () => {
    const params = new URLSearchParams(window.location.search)

    const focusApt  = params.get('focus')
    const focusShop = params.get('focus-shop')

    if (!buildingGroups.value.length) return

    // 1️⃣ حدد المجموعة الصحيحة
    let targetGroup = null

    if (focusApt) {
        targetGroup = buildingGroups.value.find(g =>
            g.apartments.some(a => String(a.id) === String(focusApt))
        )
    }

    if (!targetGroup && focusShop) {
        targetGroup = buildingGroups.value.find(g =>
            g.shops.some(s => String(s.id) === String(focusShop))
        )
    }

    selectedGroup.value = targetGroup ?? buildingGroups.value[0]

    // 2️⃣ انتظر DOM
    await nextTick()

    // 3️⃣ Scroll + Highlight
    const targetId = focusApt
        ? `apt-${focusApt}`
        : focusShop
            ? `shop-${focusShop}`
            : null

    if (!targetId) return

    setTimeout(() => {
        const el = document.getElementById(targetId)
        if (!el) return

        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        el.classList.add('ring-4', 'ring-yellow-400')

        setTimeout(() => {
            el.classList.remove('ring-4', 'ring-yellow-400')
        }, 1800)
    }, 300)
})

/* ================= الشقق حسب الطوابق ================= */
const apartmentsByFloor = computed(() => {
    if (!selectedGroup.value) return {}

    const result = {}

    selectedGroup.value.apartments.forEach(ap => {
        if (!result[ap.floor]) result[ap.floor] = []
        result[ap.floor].push(ap)
    })

    // ترتيب الشقق داخل كل طابق
    Object.keys(result).forEach(floor => {
        result[floor].sort(
            (a, b) => Number(a.number) - Number(b.number)
        )
    })

    return Object.fromEntries(
        Object.entries(result)
            .sort((a, b) => Number(a[0]) - Number(b[0]))
    )
})


/* ================= المحلات ================= */
const filteredShops = computed(() => {
    if (!selectedGroup.value) return []

    return [...selectedGroup.value.shops]
        .sort((a, b) => Number(a.number) - Number(b.number))
})

</script>



<template>
<AppLayout>
<div class="p-6">

<!-- العنوان -->
<div class="flex items-center justify-between mb-5">
    <h1 class="text-3xl font-bold text-green-700">الشقق و المحلات</h1>

    <div class="flex items-center gap-4">
<!-- زر الطباعة -->
<button
  @click="printPlan"
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

<!-- المشاريع -->
<div v-if="hasProjects" class="flex gap-6 border-b pb-2 mb-3 font-bold">
    <button
        v-for="p in projectList"
        :key="p.id"
        @click="openProject(p.id)"
        :class="selectedProject === p.id
            ? 'text-green-700 border-b-2 border-green-700'
            : 'text-gray-500 hover:text-green-700'"
    >
        {{ p.name }}
    </button>
</div>

<!-- العمارات / الأشطر -->
<div v-if="buildingGroups.length" class="flex gap-4 border-b pb-2 mb-6 font-bold">
    <button
        v-for="g in buildingGroups"
        :key="g.label"
        @click="selectedGroup = g"
        :class="selectedGroup?.label === g.label
            ? 'text-yellow-600 border-b-2 border-yellow-500'
            : 'text-gray-500 hover:text-yellow-600'"
    >
        {{ g.label }}
    </button>
</div>

        <!-- ===================== بطاقةالمحلات التجارية ===================== -->
        <div v-if="filteredShops.length" class="mb-4">

            <h3 class="text-lg font-bold text-gray-700 mb-4">
               المحلات التجارية
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <div v-for="shop in filteredShops"
                     :key="shop.id"
                     :id="`shop-${shop.id}`"
                     class="relative bg-white shadow rounded-xl p-6 pt-4 text-center hover:shadow-md hover:bg-blue-50 transition cursor-pointer"
                     @click="router.visit(`/shops/${shop.id}`)"
                >
                    <div
                        class="absolute top-2 left-2 flex items-center gap-1 bg-white border px-3 py-1 rounded-full text-xs font-bold shadow-sm"
                        :style="{ borderColor: shopStatusColor(shop.status), color: shopStatusColor(shop.status) }"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="8"/>
                        </svg>
                        {{ shop.status }}
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mt-4">
                        محل تجاري  {{ shop.number }}
                    </h3>

                    <p class="text-m text-gray-700 mt-1">
                        <span class="font-bold">{{ parseInt(shop.area) }} م²</span>
                    </p>

                    <p class="text-m text-gray-700 mt-2 font-bold">
                        صاحب المحل :
                        <span class="font-bold text-black">{{ shop.customer_name}}</span>
                    </p>

                    <!-- Progress -->
                    <div class="mt-3">

  <div class="flex justify-between items-center text-[13px] mb-1 text-gray-500">
    <span>نسبة الدفوعات</span>
    <span class="font-bold text-green-700 text-lg">
      {{ shop.payment_percentage }}%
    </span>
  </div>

  <div
    class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden"
    style="direction:ltr"
  >
    <div
      class="h-full rounded-full transition-all duration-500 ease-out"
      :style="{
        width: shop.payment_percentage + '%',
        backgroundColor: '#16a34a'
      }"
    ></div>
  </div>

                    </div>

                </div>
            </div>
        </div>
        <!-- ================= نهاية قسم المحلات ================= -->

        <div v-if="!selectedGroup" class="text-center text-gray-500 py-10 text-lg">
            لا توجد شقق
        </div>
        <!-- =====================  بطاقة الشقق ===================== -->
        <div v-else>
            <div v-for="(items, floor) in apartmentsByFloor" :key="floor" class="mb-4">
                <h3 class="text-lg font-bold text-gray-700 mb-4">
                    الطابق {{ floor == 0 ? "الأرضي" : floor }}
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    <div
                        v-for="ap in items"
                        :key="ap.id"
                        :id="`apt-${ap.id}`"
                        @click="router.visit(`/apartments/${ap.id}`)"
                        class="relative bg-white border shadow rounded-xl p-6 pt-1 text-center
                               hover:bg-green-50 hover:shadow-md transition cursor-pointer"
                    >
                        <div
                            class="absolute top-2 left-2 flex items-center gap-1 bg-white border px-3 py-1
                                   rounded-full text-xs font-bold shadow-sm"
                            :style="{ borderColor: statusColor(ap.status), color: statusColor(ap.status) }"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="8"/>
                            </svg>
                            {{ ap.status }}
                        </div>

                        <div
                            v-if="ap.parking_number"
                            class="absolute top-2 right-2 bg-white border px-3 py-1
                                   rounded-full text-xs font-bold shadow-sm"
                            style="border-color:#1E88E5; color:#1E88E5"
                        >
                            P N° {{ ap.parking_number }}
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mt-6">
                            شقة  {{ ap.number }}
                        </h3>
                        <div class="flex justify-center items-center gap-3 mt-3 text-gray-700 text-m">

  <!-- المساحة -->
  <div class="flex items-center gap-1">
    <span class="font-bold">{{ ap.area }}</span>
    <span>م²</span>

    <span v-if="ap.has_terrace" class="text-gray-600 flex items-center gap-1">
      + {{ ap.terrace_area }} م²
      <span class="font-semibold">({{ ap.terrace_type }})</span>
    </span>
  </div>

  <!-- الفاصل العمودي -->
  <span class="h-4 w-px bg-gray-300"></span>

  <!-- عدد الغرف -->
  <div class="flex items-center gap-1">
    <span class="font-semibold">{{ ap.rooms }}</span>
    <span>غرف</span>
  </div>

</div>


                        <p class="text-m text-gray-600 mt-2 font-bold">
                           صاحب الشقة :
                            <span class="font-bold text-black">{{ ap.customer_name}}</span>
                        </p>
<!-- Progress -->
<div class="mt-1">

  <!-- العنوان + النسبة -->
  <div class="flex justify-between items-center text-[13px] mb-1 text-gray-500">
    <span class="font-bold ">نسبة الدفوعات</span>
    <span class="font-bold text-green-700 text-lg">
      {{ ap.payment_percentage }}%
    </span>
  </div>

  <!-- الشريط -->
  <div
    class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden"
    style="direction:ltr"
  >
    <div
      class="h-full rounded-full transition-all duration-500 ease-out"
      :style="{
        width: ap.payment_percentage + '%',
        backgroundColor: '#16a34a'
      }"
    ></div>
  </div>

</div>


                    </div>
                </div>

            </div>
        </div>
        <!-- ================= نهاية قسم الشقق ================= -->

</div>
<!-- Print Options Modal -->
<div
  v-if="showPrintModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 text-center">

    <!-- أيقونة -->
    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-center rounded-full bg-yellow-100">
      <span class="text-3xl text-yellow-600">🖨️</span>
    </div>

    <!-- العنوان -->
    <h2 class="text-2xl font-bold text-yellow-600 mb-4">
      خيارات الطباعة
    </h2>

   <!-- الأزرار -->
<div class="space-y-4">



  <!-- بيان دفوعات العمارة (أصفر) -->
  <button
    @click="printBuildingStatement"
    class="w-full flex items-center justify-center gap-2
           px-6 py-3 rounded-xl
           bg-white border-2 border-yellow-500
           text-yellow-600 font-bold
           hover:bg-yellow-50 transition"
  >
    طباعة بيان دفوعات العمارة
  </button>

  <!-- طباعة المخطط (نفس زر المخطط) -->
  <button
    @click="printPlan"
    class="w-full flex items-center justify-center gap-2
           px-6 py-3 rounded-xl
bg-white border-2 border-green-600
           text-green-700 font-bold
           hover:bg-gray-100 transition"
  >
    طباعة المخطط
  </button>

</div>


    <!-- زر إلغاء -->
    <div class="mt-6">
      <button
        @click="showPrintModal = false"
        class="px-8 py-3 rounded-xl
               border border-gray-300
               text-gray-700 hover:bg-gray-100 transition"
      >
        إلغاء
      </button>
    </div>

  </div>
</div>

</AppLayout>
</template>