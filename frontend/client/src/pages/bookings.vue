<template>
  <div>
    <NuxtLayout title="Booking management" class="text-white flex flex-col min-h-screen" name="dashboard">
      <div class="container mx-auto px-2 md:px-4">
        <FilterBar />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Spinner :is-loading="isLoading" class="spinner" />
          <AddStudioButton title="Book recent studio" :subtitle="`${recentStudio.address} at ${recentStudio.date}`" @click="togglePopup" />
          <BookingCard @onCancelBooking="handleCancelBooking" v-for="booking in bookings" :key="booking.id" :booking="booking" />
        </div>
        <div class="flex justify-center mt-4">
          <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="px-3 py-1 border rounded-l-[10px] hover:bg-white hover:text-black">
            Previous
          </button>
          <button
              v-for="page in lastPage"
              :key="page"
              @click="goToPage(page)"
              :class="{'bg-gray-500': page === currentPage, 'bg-white': page !== currentPage}"
              class="px-3 py-1 border">
            {{ page }}
          </button>
          <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage >= lastPage"
              class="px-3 py-1 border rounded-r-[10px] hover:bg-white hover:text-black">
            Next
          </button>
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
import { AddStudioButton } from "~/src/features/addStudio";
import { onMounted, ref } from "vue";
import { BookingCard } from "~/src/entities/Booking/ui";
import { useApi } from "~/src/lib/api";
import {Spinner} from "~/src/shared/ui/common";

const showPopup = ref(false);

type BookingRecent = {
  id: number;
  name: string;
  address: string;
  date: string;
};

type Booking = {
  id: number;
  name: string;
  logo: string;
  status: number;
  isFavorite: boolean;
  address: string;
  time: string;
  date: string;
};

const recentStudio = ref<BookingRecent>({
  id: 1,
  name: 'Studio 1',
  isFavorite: true,
  address: '123 Main St',
  date: '04/07/2024',
});

const bookings = ref<Booking[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);

onMounted(() => {
  getBookings();
});

const handleCancelBooking = (bookings) =>{
  bookings.value = bookings
  getBookings()
}
const getBookings = async (page = 1) => {
  isLoading.value = true;
  const { fetch: fetchBookings } = useApi({
    url: `/booking-management?page=${page}`,
    auth: true,
  });

  fetchBookings().then((response) => {
    bookings.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    isLoading.value = false;
  }).catch((error) => {
    console.error('Error fetching bookings:', error);
  });
};

const togglePopup = () => {
  showPopup.value = !showPopup.value;
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= lastPage.value) {
    getBookings(page);
  }
};
</script>