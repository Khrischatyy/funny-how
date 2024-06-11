<template>
  <div class="text-white flex flex-col min-h-screen">
    <Header subhead="true" subhead-title="Studios" @toggleSideMenu="toggleSideMenu" />

    <div class="flex flex-1 overflow-hidden">
      <SideMenu v-if="!isLoading" :sideMenu="sideMenuArray" ref="sideMenuRef" class="lg:block lg:w-64 pl-0 md:pl-10" />
      <div v-else class="flex items-center justify-center lg:w-64">
        <div class="spinner"></div> <!-- Replace with a proper loading indicator -->
      </div>
      <div class="flex-1 overflow-auto">
        <div class="container mx-auto px-2 md:px-4">
          <FilterBar />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <AddStudioButton />
            <StudioCard v-for="studio in studios" :key="studio.id" :studio="studio" />
          </div>
        </div>
      </div>
    </div>
    <Footer class="mt-auto" />
  </div>
</template>

<style scoped>
.spinner {
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-left-color: #ffffff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>


<script setup lang="ts">
import { ref, watch } from 'vue';
import { AddStudioButton } from '~/src/features/addStudio';
import { StudioCard } from '~/src/entities/Studio';
import { Header, Footer, FilterBar } from '~/src/shared/ui/components';
import { SideMenu } from '~/src/widgets/navigation';
import { getSideMenu } from '~/src/widgets/navigation/api/useSideMenu';
import { useAsyncData } from '#app';

const sideMenuRef = ref();

const sideMenu = ref({});
const sideMenuArray = ref([]);
const isLoading = ref(true);

const { data, error } = await useAsyncData('sideMenu', getSideMenu);

watch(data, (newData) => {
  if (newData) {
    console.log('Side Menu Data:', newData.data);
    sideMenu.value = newData.data || {};
    sideMenuArray.value = Object.values(sideMenu.value);
    isLoading.value = false; // Set loading to false once data is fetched
  }
}, { immediate: true });

if (error.value) {
  console.error('Failed to fetch side menu:', error.value);
  isLoading.value = false; // Set loading to false in case of error
}

const studios = ref([
  { id: 1, logo: 'path/to/logo1.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 2, logo: 'path/to/logo2.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 3, logo: 'path/to/logo3.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
]);

const toggleSideMenu = () => {
  if (sideMenuRef.value) {
    sideMenuRef.value.toggleMenu();
  }
};

console.log('SideMenu Array:', sideMenuArray.value);
</script>
