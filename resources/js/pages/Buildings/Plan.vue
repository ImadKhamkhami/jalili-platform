<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
    building: Object,
    project: Object,
    apartments: Object
})

// العنوان الموحّد
// توليد العنوان حسب رغبة المستخدم
const fullTitle = computed(() => {

    let title = `مخطط العمارة ${buildingNumber.value}`;

    // إذا كان الشطر موجودًا
    if (selectedTranche.value) {
        title += ` – شطر ${selectedTranche.value}`;
    }

    // في الأخير اسم المشروع
    title += ` – إقامة ${props.project.name}`;

    return title;
});



/* استخراج رقم العمارة */
const buildingNumber = computed(() => {
    const match = props.building.name.match(/\d+/)
    return match ? match[0] : props.building.name
})

/* استخراج الأشطر */
const tranches = computed(() => {
    const set = new Set()

    Object.values(props.apartments).forEach(floorList => {
        floorList.forEach(ap => {
            if (ap.tranche_number) set.add(ap.tranche_number)
        })
    })

    return Array.from(set).sort((a, b) => a - b)
})

const selectedTranche = ref(null)
// اختيار أول شطر تلقائياً
watch(tranches, (list) => {
    if (list.length > 0 && !selectedTranche.value) {
        selectedTranche.value = list[0];
    }
}, { immediate: true });
/* فلترة حسب الشطر */
const filteredFloors = computed(() => {
    if (!selectedTranche.value) return props.apartments

    const result = {}

    Object.entries(props.apartments).forEach(([floor, floorList]) => {
        const filtered = floorList.filter(ap =>
            ap.tranche_number == selectedTranche.value
        )
        if (filtered.length) result[floor] = filtered
    })

    return result
})

/* ألوان الحالة */
const statusColor = (s) => {
    switch (s) {
        case 'متاحة': return '#2E7D32'
        case 'مباعة': return '#E53935'
        case 'محجوزة': return '#F4A300'
        default: return '#444'
    }
}
</script>

<template>
<AppLayout>

    <div class="p-8">



<div class="flex items-center justify-between mb-10">

    <!-- العنوان -->
    <div class="text-right">
        <h1 class="text-3xl font-extrabold text-green-700">
            مخطط العمارة {{ buildingNumber }}
            <span v-if="tranches.length">– شطر {{ selectedTranche }}</span>
            – إقامة {{ project.name }}
        </h1>
    </div>
        <!-- زر تحميل PDF -->
<div class="flex justify-end mb-6">
    <a
        :href="`/buildings/${building.id}/plan/pdf`"
        target="_blank"
        class="flex items-center gap-2 px-5 py-2 rounded-full border-2 border-yellow-500 text-yellow-600 hover:bg-yellow-50 transition"
    >
        <!-- أيقونة PDF -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM14 2v6h6M9 15v2m0-6v2m3-2h2a2 2 0 110 4h-2v4" />
        </svg>

        تحميل PDF
    </a>
</div>


</div>

        <!-- شريط الأشطر (تصميم ذهبي مطابق لصفحة الشقق) -->
        <div
            v-if="tranches.length"
            class="flex justify-start gap-6 border-b pb-2 mb-8 text-base font-bold"
        >
            <button
                v-for="t in tranches"
                :key="t"
                @click="selectedTranche = t"
                class="pb-2 transition cursor-pointer"
                :class="selectedTranche === t
                    ? 'text-yellow-600 border-b-2 border-yellow-500'
                    : 'text-gray-500 hover:text-yellow-600'"
            >
                شطر {{ t }}
            </button>

        </div>

        <!-- عرض الطوابق -->
        <div
            v-for="(floorList, floor) in filteredFloors"
            :key="floor"
            class="mb-14"
        >

            <h2 class="text-xl font-bold text-gray-700 mb-6 text-right">
                الطابق {{ floor == 0 ? "الأرضي" : floor }}
            </h2>

            <!-- الشبكة تحوي حتى 6 بطاقات في السطر -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">

                <div
                    v-for="ap in floorList"
                    :key="ap.id"
                    class="relative bg-white shadow-md rounded-xl p-5 border hover:shadow-lg transition cursor-pointer w-[220px]"
                >

                    <!-- حالة -->
                    <div
                        class="absolute top-2 left-2 bg-white px-3 py-1 rounded-full border text-xs font-bold shadow-sm flex items-center gap-1"
                        :style="{ borderColor: statusColor(ap.status), color: statusColor(ap.status) }"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="8" />
                        </svg>
                        {{ ap.status }}
                    </div>

                    <!-- موقف -->
                    <div v-if="ap.parking_number"
                        class="absolute top-2 right-2 bg-white px-3 py-1 rounded-full border text-xs font-bold shadow-sm"
                        style="border-color:#1E88E5; color:#1E88E5">
                        P N° {{ ap.parking_number }}
                    </div>

                    <!--                     <div v-if="ap.has_terrace"
                        class="absolute top-2 right-24 bg-white px-3 py-1 rounded-full border text-xs font-bold shadow-sm"
                        style="border-color:#D4A017; color:#D4A017">
                        {{ ap.terrace_type }} {{ ap.terrace_area }}m²
                    </div>تيراس -->


                    <!-- رقم الشقة -->
                    <h3 class="text-xl font-bold text-gray-900 mt-6 mb-1">
                        شقة رقم {{ ap.number }}
                    </h3>

                    <!-- المساحة + التيراس -->
                    <p class="text-sm text-gray-700">
                        المساحة:
                        <span class="font-bold">

                            <template v-if="ap.has_terrace">
                                {{ ap.area }} + {{ ap.terrace_area }} م²
                                <span class="text-xs text-gray-500">({{ ap.terrace_type }})</span>
                            </template>

                            <template v-else>
                                {{ ap.area }} م²
                            </template>

                        </span>
                    </p>

                    <!-- الغرف -->
                    <p class="text-sm text-gray-700 mt-1">
                        عدد الغرف:
                        <span class="font-bold">{{ ap.rooms }}</span>
                    </p>

                    <!-- الزبون -->
                    <p class="text-sm text-gray-700 mt-2">
                        صاحب الشقة:
                        <span class="font-bold">{{ ap.customer_name || "غير محدد" }}</span>
                    </p>

                </div>

            </div>

        </div>

    </div>

</AppLayout>
</template>
