<template>
  <div class="text-white flex flex-col min-h-screen">
    <Header />
    <div class="flex flex-1 overflow-hidden">
      <SideMenu class="w-64"/>
      <div class="flex-1 overflow-auto">
        <div class="container mx-auto px-2 sm:px-4 py-6 pt-16 lg:pt-6">
          <div class="flex justify-between items-center mb-6">
            <div class="lg:hidden">
              <button @click="sideMenu.value.toggleMenu" class="text-white">
                <i class="fas fa-bars fa-2x"></i>
              </button>
            </div>
            <h1 class="text-2xl font-bold">Studios</h1>
          </div>
          <div class="flex mb-6">
            <SearchBar />
            <FilterButtons />
          </div>
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
import { ref } from 'vue';
import { AddStudioButton } from '~/src/features/addStudio';
import { StudioCard } from '~/src/entities/Studio';
import { Header, Footer, SearchBar, FilterButtons } from '~/src/shared/ui/components';
import { SideMenu } from '~/src/widgets/navigation';

const sideMenu = ref();

const studios = ref([
  { id: 1, logo: 'path/to/logo1.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 2, logo: 'path/to/logo2.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 3, logo: 'path/to/logo3.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
]);
</script>
