<script setup lang="ts">
 import { dashboard, login, register } 
 from '@/routes'; import { Head, Link } 
 from '@inertiajs/vue3';
  withDefaults( defineProps<{ canRegister: boolean; }>(),
   { canRegister: true, }, ); 
   </script>
    <template>
     <Head title="Welcome"> 
        <link rel="preconnect" href="https://rsms.me/" /> 
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" /> 
        </Head> <div class="flex min-h-screen flex-col items-center justify-center bg-[#FAFAF8] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
             <!-- شعار + نص + زر داخل كارد --> 
              <div class="w-full max-w-md bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.07)] p-10 text-center" > 
                <!-- الشعار --> 
                 <img src="/images/logo-jalili.jpg" alt="Jalili Logo" class="w-40 h-auto mx-auto mb-6" />
                  <!-- العنوان -->
                    <h1 class="text-2xl font-bold text-[#0A5A55] mb-3"> لإدارة المشاريع العقارية </h1>
                     <!-- الوصف --> 
                      <p class="text-gray-600 text-base leading-7 mb-6"> منصة جليلي إخوان لتسيير ومتابعة المباني، الشقق، والملفات الهندسية بكل سهولة. </p> 
                      <!-- زر الدخول --> 
                       <Link :href="login()" class="block w-full bg-[#0A5A55] hover:bg-[#074a45] text-white py-3 rounded-lg text-lg font-semibold transition" > الدخول إلى المنصة </Link> 
                       </div> 
                       </div> 
                       </template> 
                       <style> 
                       /* أنيميشن لطيفة احترافية */
                        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); }
                         to { opacity: 1; transform: translateY(0); } } @keyframes scaleIn { from { opacity: 0; transform: scale(0.85); 
                        } to { opacity: 1; transform: scale(1);
                         } } @keyframes slideUp { from { opacity: 0; transform: translateY(25px); 
                        } to { opacity: 1; transform: translateY(0); 
                        } } .animate-fadeIn { animation: fadeIn 1s ease forwards;
                         } .animate-scaleIn { animation: scaleIn 0.9s ease forwards;
                         } .animate-slideUp { animation: slideUp 1.2s ease forwards; }
 </style>