<template>
  <div class="text-white flex flex-col min-h-screen">
    <Header :subhead="true" :show-menu="true" :subhead-title="$attrs.title as string" @toggleSideMenu="toggleSideMenu" />
    <div class="flex flex-1 overflow-hidden">
      <SideMenu :is-data-loading="isLoading" :sideMenu="$attrs?.sideMenu || sideMenuTemplate" ref="sideMenuRef" class="lg:block lg:w-64 pl-0 md:pl-10" />
      <div class="flex-1 overflow-auto">


        <div v-if="$attrs.isChildLoading" class="flex items-center justify-center absolute z-[11] left-0 top-0 rounded-[10px] w-full h-full bg-black bg-opacity-70">
          <div class="spinner"></div> <!-- Replace with a proper loading indicator -->
        </div>

        <slot/>
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
import {ref, watch, onMounted, useAttrs, reactive, type Component} from 'vue';
import { Header, Footer} from '~/src/shared/ui/components';
import { SideMenu } from '~/src/widgets/navigation';
import { getSideMenu } from '~/src/widgets/navigation/api/useSideMenu';
import { useAsyncData } from '#app';
import {STUDIO_OWNER_ROLE, USER_ROLE} from "~/src/entities/Session";

const sideMenuRef = ref();
const isLoading = ref(false);
const { data, error } = await useAsyncData('sideMenu', getSideMenu);
interface MenuItem {
  icon: Component;
  name: string;
  path: string;
  link?: string;
}
import {IconMic, IconBooking, IconUser, IconClients, IconHistory, IconClose} from "~/src/shared/ui/common";

const sideMenuTemplate: MenuItem[] = [
  { name: 'Profile', icon: IconUser, path: '/icons/profile.svg', link: '/settings/role', role: '' },
  { name: 'Studios', icon: IconMic, path: '/icons/profile.svg', link: '/studios', role: USER_ROLE },
  { name: 'Booking management', icon: IconBooking, path: '/icons/profile.svg', link: '/bookings', role: '' },
  { name: 'My Studios', icon: IconMic, path: '/icons/my-studios.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'Clients', icon: IconClients, path: '/icons/settings.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'History', icon: IconHistory, path: '/icons/settings.svg', link: '/my-studios', role: STUDIO_OWNER_ROLE },
  { name: 'Logout', icon: IconClose, path: '/icons/logout.svg', link: '/logout', role: '' }
];
if (error.value) {
  console.error('Failed to fetch side menu:', error.value);
  isLoading.value = false; // Set loading to false in case of error
}

interface MenuItem {
  icon: Component;
  name: string;
  path: string;
  link?: string;
  role?: string;
}

const toggleSideMenu = () => {
  if (sideMenuRef.value) {
    sideMenuRef.value.toggleMenu();
  }
};

</script>