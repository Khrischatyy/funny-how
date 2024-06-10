<template>
  <div class="text-white flex flex-col min-h-screen">
    <Header />
    <div class="flex flex-1 overflow-hidden">
      <SideMenu :sideMenu="sideMenuArray" ref="sideMenuRef" class="w-64"/>
      <div class="flex-1 overflow-auto">
        <div class="container mx-auto px-2 sm:px-4 py-6 pt-16 lg:pt-6">
          <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Studios</h1>
            <button class="lg:hidden p-2" @click="toggleSideMenu">
              <i class="fas fa-bars fa-2x text-white"></i>
            </button>
          </div>
          <FilterBar />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <AddStudioButton />
            <StudioCard
                v-for="studio in studios"
                :key="studio.id"
                :studio="studio"
            />
          </div>
        </div>
      </div>
    </div>
    <Footer class="mt-auto" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { AddStudioButton } from '~/src/features/addStudio';
import { StudioCard } from '~/src/entities/Studio';
import { Header, Footer, FilterBar } from '~/src/shared/ui/components';
import { SideMenu } from '~/src/widgets/navigation';
import { getSideMenu } from '~/src/widgets/navigation/api/useSideMenu';

const sideMenuRef = ref();

const sideMenu = ref({});
const sideMenuArray = ref([]);

const fetchSideMenu = async () => {
  try {
    const menuData = await getSideMenu();
    sideMenu.value = menuData.data;
    sideMenuArray.value = Object.values(menuData.data);
  } catch (error) {
    console.error('Failed to fetch side menu:', error);
  }
};

onMounted(() => {
  fetchSideMenu();
});

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
</script>
