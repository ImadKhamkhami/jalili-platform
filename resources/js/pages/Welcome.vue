<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    canRegister: boolean
  }>(),
  {
    canRegister: true,
  }
)
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#FAFAF8] to-[#F0F5F4] px-6"
  >
    <!-- Card -->
    <div
      class="w-full max-w-md bg-white rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.08)] p-10 text-center animate-scaleIn"
    >
      <!-- Logo -->
      <img
        src="/images/logo-jalili.jpg"
        alt="Jalili Freres"
        class="w-36 h-auto mx-auto mb-6"
      />

      <!-- Title -->
      <h1 class="text-2xl font-extrabold text-[#0A5A55] mb-3">
        لإدارة المشاريع العقارية
      </h1>

      <!-- Description -->
      <p class="text-gray-600 text-base leading-7 mb-8">
        منصة <strong>جليلي إخوان</strong> لتسيير المباني و التجزئات 
        والملفات  بكل سهولة واحترافية.
      </p>

      <!-- Login Button -->
      <Link
        href="/login"
        class="block w-full bg-[#0A5A55] hover:bg-[#074a45] text-white py-3.5 rounded-xl text-lg font-bold tracking-wide transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5"
      >
        الدخول إلى المنصة
      </Link>

    </div>
  </div>
</template>

<style scoped>
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scaleIn {
  animation: scaleIn 0.8s ease-out forwards;
}
</style>
