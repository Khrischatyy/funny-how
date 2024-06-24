<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col gap-5 justify-between">
    <div class="flex justify-between items-center">
      <div class="flex justify-start items-center gap-5">
        <div class="h-[35px] w-[35px]">
          <img :src="booking.logo" alt="Logo" class="h-auto w-full object-fill" />
        </div>
        <div>
          <h3 class="text-xl font-bold text-white">{{ booking.name }}</h3>
          <p :class="getColor(booking.status)" class="font-['Montserrat']">{{ getStatus(booking.status) }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3 cursor-pointer hover:opacity-70">
        <IconLike :icon-active="booking.isFavorite" :icon-color="booking.isFavorite ? '#FD9302' : 'white'" />
      </div>
    </div>
    <div class="flex gap-3 justify-between items-center relative">
      <div class="w-full relative">
        <p class="text-white">{{ booking.address }}</p>
      </div>
    </div>

    <div class="flex gap-3 justify-between items-center">
      <div class="flex items-center relative gap-2 group-hours-block group">
        <IconCalendar class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Date</span>
          <span class="text-white">{{ booking.date }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2 relative group-price group">
        <IconClock class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Time</span>
          <span class="text-white">{{ booking.time }}</span>
        </div>
      </div>
    </div>
    <button class="w-full h-11 hover:opacity-90 bg-white rounded-[10px] text-neutral-900 text-sm font-medium tracking-wide">
      Manage Booking
    </button>
  </div>
</template>

<script setup lang="ts">
import {IconCalendar, IconClock, IconLeft, IconLike, IconRight} from "~/src/shared/ui/common";

const STATUSES = {
  1: 'Booking Accepted',
  2: 'Booking Declined',
  3: 'Waiting To Accept'
};

const STATUS_COLOR = {
  1: 'text-green',
  2: 'text-red',
  3: 'text-yellow'
};

const getStatus = (status: number) => STATUSES[status as keyof typeof STATUSES];
const getColor = (status: number) => STATUS_COLOR[status as keyof typeof STATUS_COLOR];
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

const props = defineProps<{
  booking: Booking;
}>();

</script>

<style scoped>
</style>