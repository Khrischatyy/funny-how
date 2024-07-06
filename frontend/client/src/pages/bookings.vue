<template>
  <div>
    <NuxtLayout title="Booking management" class="text-white flex flex-col min-h-screen" name="dashboard">
      <div class="container mx-auto px-2 md:px-4">
        <FilterBar :filters-show="filterShow" @update:filters="handleFiltersChange" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Spinner :is-loading="isLoading" />
          <AddStudioButton title="Book recent studio" :subtitle="`${recentStudio.address} at ${recentStudio.date}`" @click="togglePopup" />
          <BookingCard @on-favorite-change="onFavoriteChange" @onCancelBooking="handleCancelBooking" v-for="booking in bookings" :key="booking.id" :booking="booking" />
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

</style>

<script setup lang="ts">
import { FilterBar  } from '~/src/shared/ui/components';
import { AddStudioButton } from "~/src/features/addStudio";
import {onMounted, reactive, ref} from "vue";
import { BookingCard } from "~/src/entities/Booking/ui";
import { useApi } from "~/src/lib/api";
import {Spinner} from "~/src/shared/ui/common";
import {filterUnassigned} from "~/src/shared/utils";

const showPopup = ref(false);
const filterShow = reactive([
  {key: 'search', options:'', value: ''},
  {key: 'status', options: [{id: 1, name: 'Status 1'}, {id: 2, name: 'Status 2'}], value: ''},
  {key: 'date', options:'', value: ''},
  {key: 'time', options:'', value: ''}]);

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

const handleFiltersChange = (newFilters) => {
  filterShow.forEach((filter) => {
    filter.value = newFilters[filter.key];
  });
  getBookings(1); // Reset to page 1 with new filters
};

const bookings = ref<Booking[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);

onMounted(() => {
  getBookings();
});

const onFavoriteChange = (bookingId) => {
  bookings.value = bookings.value.map((booking) => {
    if (booking.id === bookingId) {
      booking.address.is_favorite = !booking.address.is_favorite;
    }
    return booking;
  });
};
const handleCancelBooking = (bookings) =>{
  bookings.value = bookings
  getBookings()
}
const getBookings = async (page = 1) => {
  isLoading.value = true;
  const { post: fetchBookings } = useApi({
    url: `/booking-management/filter?page=${page}`,
    auth: true,
  });

  const body = filterShow.reduce((acc, filter) => {
    acc[filter.key] = filter.value;
    return acc;
  }, {});

  fetchBookings(filterUnassigned(body)).then((response) => {
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