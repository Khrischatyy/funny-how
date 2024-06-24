<template>
  <div>
    <NuxtLayout title="Booking management" class="text-white flex flex-col min-h-screen" name="dashboard">
      <div class="container mx-auto px-2 md:px-4">
        <FilterBar />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <AddStudioButton title="Book recent studio" :subtitle="`${recentStudio.address} at ${recentStudio.date}`"  @click="togglePopup" />
          <BookingCard v-for="booking in bookings" :key="booking.id" :booking="booking" />
        </div>
      </div>
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
import { FilterBar  } from '~/src/shared/ui/components';
import {AddStudioButton} from "~/src/features/addStudio";
import {ref} from "vue";
import {navigateTo} from "nuxt/app";
import {StudioCard} from "~/src/entities/Studio";
import {BookingCard} from "~/src/entities/Booking/ui";
const showPopup = ref(false);

type Booking = {
  id: number;
  address: string;
  date: string;
};

const recentStudio = ref<Booking>({
  id: 1,
  name: 'Studio 1',
  logo: '/logo.png',
  status: 1,
  isFavorite: true,
  address: '123 Main St',
  time: '10:00 AM - 12:00 PM',
  date: '04/07/2024',
});

const bookings = ref<Booking[]>([
  {
    id: 1,
    name: 'Studio 1',
    logo: '/logo.png',
    status: 1,
    isFavorite: true,
    address: '123 Main St',
    time: '10:00 AM - 12:00 PM',
    date: '04/07/2024',
  },
  {
    id: 2,
    name: 'Studio 1',
    logo: '/logo.png',
    status: 3,
    isFavorite: false,
    address: '123 Main St',
    time: '10:00 AM - 12:00 PM',
    date: '04/07/2024',
  },
  {
    id: 1,
    name: 'Studio 2',
    logo: '/meta/favicon.svg',
    status: 2,
    isFavorite: false,
    address: '123 Main St',
    time: '10:00 AM - 12:00 PM',
    date: '04/07/2024',
  },
  ]
);
const togglePopup = () => {
  showPopup.value = !showPopup.value;
};
</script>