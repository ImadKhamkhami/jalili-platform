<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import NavUser from '@/components/NavUser.vue'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
} from '@/components/ui/sidebar'

import {
  LayoutGrid,
  Building2,
  Folder,
  Home,
  Landmark,
  Users,
  Receipt,
  PlusCircle,
  ChevronDown,
  UserPlus,
  Search ,
  ArrowLeftRight
} from 'lucide-vue-next'





const usersOpen = ref(false)

function isUsersRoute() {
  return page.url.startsWith('/users')
}


const page = usePage()

/* ================= PATH HELPERS ================= */
const currentPath = () => page.url.split('?')[0]

const isExact = (path: string) => currentPath() === path
const starts = (base: string) => currentPath().startsWith(base)

/* ================= ACTIVE LOGIC (FINAL) ================= */
const isCompaniesActive = () =>
  starts('/companies')

const isProjectsActive = () => {
  const path = currentPath()
  return path === '/projects' || path === '/projects/create'
}
const isTransfersActive = () =>
  currentPath().startsWith('/transfers')


const isUnitsActive = () =>
  starts('/apartments') || starts('/shops')

const isLandsActive = () => {
  const path = currentPath()
  return (
    path === '/lands' ||
    path === '/lands/create' ||
    path.includes('/lands')
  )
}

/* ================= COLLAPSIBLE ================= */
const companiesOpen = ref(true)
const projectsOpen = ref(true)
const unitsOpen = ref(true)
const landsOpen = ref(true)

/* ================= MOBILE ================= */
const isMobile = () => window.matchMedia('(max-width: 768px)').matches

onMounted(() => {
  if (isMobile()) {
    companiesOpen.value = false
    projectsOpen.value = false
    unitsOpen.value = false
    landsOpen.value = false
  }
})

/* ================= AUTO OPEN ================= */
watch(
  () => currentPath(),
  (url) => {
    companiesOpen.value = url.startsWith('/companies')
    projectsOpen.value = url === '/projects' || url === '/projects/create'
    unitsOpen.value = url.startsWith('/apartments') || url.startsWith('/shops')
    landsOpen.value = url.includes('/lands')
    usersOpen.value = url.startsWith('/users')
  },
  { immediate: true }
)
</script>

<template>
  <Sidebar
    side="right"
    collapsible="icon"
    class="fixed right-0 top-0 h-screen w-64 bg-[#E8F3ED] border-l border-green-200 shadow-sm flex flex-col overflow-hidden"
  >
    <!-- ================= HEADER ================= -->
    <SidebarHeader class="py-8 px-2 text-center bg-[#E8F3ED]">
      <Link href="/dashboard">
        <img
          src="/images/jalili-logo.png"
          class="w-40 mx-auto group-data-[collapsible=icon]:w-6"
          alt="Jalili Freres"
        />
      </Link>
    </SidebarHeader>

    <!-- ================= CONTENT ================= -->
    <SidebarContent class="flex-1 px-2 bg-[#E8F3ED] overflow-hidden">
      <SidebarMenu class="space-y-1 text-right text-[15px]">

        <!-- الرئيسية -->
       <SidebarMenuButton
  as-child
  :class="isExact('/dashboard')
    ? 'bg-green-200 font-bold'
    : 'hover:bg-green-200'"
>
  <Link href="/dashboard" class="flex items-center gap-3 px-4 py-2 rounded-lg">
    <LayoutGrid class="w-5 h-5" />
    <span class="group-data-[collapsible=icon]:hidden font-bold">
      الرئيسية
    </span>
  </Link>
       </SidebarMenuButton>
        <!-- الشركات -->
        <SidebarMenuButton
          @click="companiesOpen = !companiesOpen"
          :class="[
            'flex items-center justify-between px-4 py-3 rounded-lg',
            isCompaniesActive() ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
          ]"
        >
          <div class="flex items-center gap-3">
            <Building2 class="w-5 h-5" />
            <span class="group-data-[collapsible=icon]:hidden font-bold">الشركات</span>
          </div>
          <ChevronDown class="w-4 h-4" :class="companiesOpen && 'rotate-180'" />
        </SidebarMenuButton>

        <div v-show="companiesOpen" class="pr-4 space-y-1">
          <Link
            href="/companies"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/companies') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <Building2 class="w-4 h-4" /> القائمة
          </Link>
          <Link
            href="/companies/create"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/companies/create') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <PlusCircle class="w-4 h-4" /> إضافة شركة
          </Link>
        </div>

        <!-- المشاريع -->
        <SidebarMenuButton
          @click="projectsOpen = !projectsOpen"
          :class="[
            'flex items-center justify-between px-4 py-3 rounded-lg',
            isProjectsActive() ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
          ]"
        >
          <div class="flex items-center gap-3">
            <Folder class="w-5 h-5" />
            <span class="group-data-[collapsible=icon]:hidden font-bold">المشاريع</span>
          </div>
          <ChevronDown class="w-4 h-4" :class="projectsOpen && 'rotate-180'" />
        </SidebarMenuButton>

        <div v-show="projectsOpen" class="pr-4 space-y-1">
          <Link
            href="/projects"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/projects') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <Folder class="w-4 h-4" /> القائمة
          </Link>
          <Link
            href="/projects/create"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/projects/create') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <PlusCircle class="w-4 h-4" /> إضافة مشروع
          </Link>
        </div>

        <!-- الشقق والمحلات -->
        <SidebarMenuButton
          @click="unitsOpen = !unitsOpen"
          :class="[
            'flex items-center justify-between px-4 py-3 rounded-lg',
            isUnitsActive() ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
          ]"
        >
          <div class="flex items-center gap-3">
            <Home class="w-5 h-5" />
            <span class="group-data-[collapsible=icon]:hidden font-bold">الشقق والمحلات</span>
          </div>
          <ChevronDown class="w-4 h-4" :class="unitsOpen && 'rotate-180'" />
        </SidebarMenuButton>

        <div v-show="unitsOpen" class="pr-4 space-y-1">
          <Link
            href="/apartments"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/apartments') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <Home class="w-4 h-4" /> القائمة
          </Link>
          <Link
            href="/apartments/create"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/apartments/create') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <PlusCircle class="w-4 h-4" /> إضافة شقة
          </Link>
          <Link
            href="/shops/create"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/shops/create') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <PlusCircle class="w-4 h-4" /> إضافة محل
          </Link>
        </div>

        <!-- القطع الأرضية -->
        <SidebarMenuButton
          @click="landsOpen = !landsOpen"
          :class="[
            'flex items-center justify-between px-4 py-3 rounded-lg',
            isLandsActive() ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
          ]"
        >
          <div class="flex items-center gap-3">
            <Landmark class="w-5 h-5" />
            <span class="group-data-[collapsible=icon]:hidden font-bold">القطع الأرضية</span>
          </div>
          <ChevronDown class="w-4 h-4" :class="landsOpen && 'rotate-180'" />
        </SidebarMenuButton>

        <div v-show="landsOpen" class="pr-4 space-y-1">
          <Link
            href="/lands"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/lands') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <Landmark class="w-4 h-4" /> القائمة
          </Link>
          <Link
            href="/lands/create"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-lg',
              isExact('/lands/create') ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
            ]"
          >
            <PlusCircle class="w-4 h-4" /> إضافة قطعة
          </Link>
        </div>

        <!-- الدفوعات -->
       <SidebarMenuButton
  as-child
  :class="isExact('/payments')
    ? 'bg-green-200 font-bold'
    : 'hover:bg-green-200'"
>
  <Link href="/payments" class="flex items-center gap-3 px-4 py-3 rounded-lg">
    <Receipt class="w-5 h-5" />
    <span class="group-data-[collapsible=icon]:hidden font-bold">
      الدفوعات
    </span>
  </Link>
       </SidebarMenuButton>


        <!-- التنازلات -->
        <SidebarMenuButton
  as-child
  :class="isTransfersActive()
    ? 'bg-green-200 font-bold'
    : 'hover:bg-green-200'"
>
  <Link href="/transfers" class="flex items-center gap-3 px-4 py-2 rounded-lg">
    <ArrowLeftRight class="w-5 h-5" />
    <span class="group-data-[collapsible=icon]:hidden font-bold">
      التنازلات
    </span>
  </Link>
        </SidebarMenuButton>


         <!-- البحث عن زبون  -->
        <SidebarMenuButton
  as-child
  :class="isExact('/customers')
    ? 'bg-green-200 font-bold'
    : 'hover:bg-green-200'"
>
  <Link href="/customers" class="flex items-center gap-3 px-4 py-2 rounded-lg">
    <Search class="w-5 h-5" />
    <span class="group-data-[collapsible=icon]:hidden font-bold">
      البحث عن زبون
    </span>
  </Link>
        </SidebarMenuButton>


<!-- المستخدمون -->
<SidebarMenuButton
  @click="usersOpen = !usersOpen"
  :class="[
    'flex items-center justify-between px-4 py-3 rounded-lg',
    isUsersRoute() ? 'bg-green-200 font-bold' : 'hover:bg-green-200'
  ]"
>
  <div class="flex items-center gap-3">
    <Users class="w-5 h-5" />
    <span class="group-data-[collapsible=icon]:hidden font-bold">
      المستخدمون
    </span>
  </div>

  <ChevronDown
    class="w-4 h-4 transition-transform"
    :class="usersOpen && 'rotate-180'"
  />
</SidebarMenuButton>
<!-- فروع المستخدمين -->
<div v-show="usersOpen" class="ml-6 mt-1 space-y-1">

  <!-- قائمة المستخدمين -->
  <Link
    href="/users"
    :class="[
      'flex items-center gap-3 px-4 py-2 rounded-lg text-sm',
      isExact('/users')
        ? 'bg-green-300 font-bold'
        : 'hover:bg-green-200'
    ]"
  >
    <Users class="w-4 h-4" />
    <span class="group-data-[collapsible=icon]:hidden">
      القائمة
    </span>
  </Link>

  <!-- إضافة مستخدم -->
  <Link
    href="/users/create"
    :class="[
      'flex items-center gap-3 px-4 py-2 rounded-lg text-sm',
      isExact('/users/create')
        ? 'bg-green-300 font-bold'
        : 'hover:bg-green-200'
    ]"
  >
    <UserPlus class="w-4 h-4" />
    <span class="group-data-[collapsible=icon]:hidden">
      إضافة مستخدم
    </span>
  </Link>

</div>


      </SidebarMenu>
    </SidebarContent>

    <!-- ================= FOOTER ================= -->
    <SidebarFooter class="border-t border-green-200 px-3 py-4 bg-[#E8F3ED]">
      <NavUser />
    </SidebarFooter>
  </Sidebar>

  <slot />
</template>