<template>
    <div>
      <NuxtLayout @toggleSideMenu="toggleSideMenu" title="Studios" class="text-white flex flex-col min-h-screen" name="dashboard">
        <div class="container mx-auto px-2 md:px-4">
          <FilterBar />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <AddStudioButton @click="navigateTo('/create')" />
            <StudioCard v-for="studio in myStudios" @click="editStudio(studio)" :key="studio.id" :studio="studio" />
          </div>
        </div>
        <AddStudioModal v-if="showPopup" :show-popup="showPopup" @closePopup="closePopup" @togglePopup="togglePopup" />
      </NuxtLayout>
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
import {ref, watch, onMounted, type Component, inject, provide} from 'vue';
import { AddStudioButton } from '~/src/features/addStudio';
import { StudioCard } from '~/src/entities/Studio';
import { Header, Footer, FilterBar, Popup } from '~/src/shared/ui/components';
import { SideMenu } from '~/src/widgets/navigation';
import { getSideMenu } from '~/src/widgets/navigation/api/useSideMenu';
import { useAsyncData } from '#app';
import { AddStudioModal } from "~/src/widgets/Modals";
import { getMyStudios } from '~/src/entities/RegistrationForms/api/getMyStudios';
import {IconBooking, IconClients, IconClose, IconHistory, IconMic, IconUser} from "~/src/shared/ui/common";
import {STUDIO_OWNER_ROLE, USER_ROLE} from "~/src/entities/Session";
import {navigateTo} from "nuxt/app";

const sideMenuRef = ref();
const sideMenuArray = ref([]);
const isLoading = ref(true);
const myStudios = ref([]);
const showPopup = ref(false);

type Studio = {
  name: string,
  address: string,
  description: string,
  hours: string,
  price: number,
  logo: string,
  badges: string[],
  equipment: string[]
}

const fetchStudios = async () => {
  const studios = await getMyStudios();
  myStudios.value = studios;
};

const { data, error } = await useAsyncData('sideMenu', getSideMenu);


const togglePopup = () => {
  showPopup.value = !showPopup.value;
};

const studioForPopup = ref<Studio | null>(null);

const editStudio = (studio:any) => {
  studioForPopup.value = studio;
  showPopup.value = true;
}
provide('studioForPopup', studioForPopup);

const closePopup = () => {
  showPopup.value = false;
};

if (error.value) {
  console.error('Failed to fetch side menu:', error.value);
  isLoading.value = false; // Set loading to false in case of error
}

const toggleSideMenu = () => {
  if (sideMenuRef.value) {
    sideMenuRef.value.toggleMenu();
  }
};

onMounted(() => {
  fetchStudios();
});
</script>