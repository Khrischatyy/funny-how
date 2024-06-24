<template>
    <NuxtLayout @toggleSideMenu="toggleSideMenu" :sideMenu="sideMenu" title="Studios" class="text-white flex flex-col min-h-screen" name="dashboard">
          <div class="container mx-auto px-2 md:px-4">
            <FilterBar />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <AddStudioButton @click="togglePopup" />
              <StudioCard v-for="studio in myStudios" @click="navigateTo(`/@${studio.company_slug}/studio/${studio.id}`)" :key="studio.id" :studio="studio" />
            </div>
          </div>
      <AddStudioModal :show-popup="showPopup" @closePopup="closePopup" @togglePopup="togglePopup" />
    </NuxtLayout>
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
import {ref, watch, onMounted, type Component} from 'vue';
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

const fetchStudios = async () => {
  const studios = await getMyStudios();
  myStudios.value = studios;
};

const { data, error } = await useAsyncData('sideMenu', getSideMenu);
interface MenuItem {
  icon: Component;
  name: string;
  path: string;
  link?: string;
}

const sideMenu: MenuItem[] = [
  { name: 'Studios', icon: IconMic, path: '/icons/profile.svg', link: '/studios', role: USER_ROLE },
  { name: 'Booking management', icon: IconBooking, path: '/icons/profile.svg', link: '/studios', role: USER_ROLE },
  { name: 'Profile', icon: IconUser, path: '/icons/profile.svg', link: '/settings/role', role: '' },
  { name: 'My Studios', icon: IconMic, path: '/icons/my-studios.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'Clients', icon: IconClients, path: '/icons/settings.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'History', icon: IconHistory, path: '/icons/settings.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'Logout', icon: IconClose, path: '/icons/logout.svg', link: '/logout', role: '' }
];

const togglePopup = () => {
  showPopup.value = !showPopup.value;
};

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