<script setup> import { ref } from 'vue';
 import { router } from '@inertiajs/vue3'; 
 const query = ref(''); const search = () => { router.get('/projects', { search: query.value }); }; 
 </script>
  <template> 
  <div class="w-full h-full flex items-start justify-center bg-[#FAFAFA] pt-30">
     <div class="text-center flex flex-col items-center"> 
        <!-- شعار أكبر --> <img src="/images/jalili-logo.png" alt="Jalili Frères" class="w-130 h-auto mb-8 drop-shadow-md" />
          <!-- <h1 class="text-5xl font-extrabold text-green-800 mb-10"> جليلـي إخــوان للعقــار </h1> عنوان أكبر --> <!-- مربع البحث أكبر --> 
           <!--<div class="flex items-center gap-4 bg-white px-6 py-2 rounded-3xl shadow-lg w-[700px] hover:shadow-xl transition"> <input v-model="query" type="text" placeholder="ابحث عن مشروع..." class="flex-1 bg-transparent outline-none text-right text-Xl placeholder-gray-400" > <button @click="search" class="px-8 py-3 bg-green-800 hover:bg-green-800 text-white rounded-2xl text-xl font-semibold transition" > بحث </button> </div> --> 
        </div>
    </div>
 </template>