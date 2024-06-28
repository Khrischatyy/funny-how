<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col sm:flex-row gap-5 justify-between">
    <div class="flex w-auto justify-between items-center">
      <div class="flex justify-start  items-center gap-5">
        <div class="h-[35px] w-[35px]">
          <img :src="booking.logo" alt="Logo" class="h-auto w-full object-cover" />
        </div>
        <div>
          <h3 class="text-xl font-bold text-white">{{ booking.name }}</h3>
          <p class="font-['Montserrat']">Client name</p>
        </div>
      </div>
    </div>
    <div class="flex w-auto flex-col sm:flex-row gap-8 min-w-[210px] justify-center items-start sm:items-center">
      <div class="flex items-center relative gap-2 ">
        <IconAddress class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Address</span>
          <p class="text-white">{{ booking.address }}</p>
        </div>
      </div>
      <div class="flex items-center relative gap-2 group-hours-block group">
        <IconCalendar class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Date</span>
          <span class="text-white">{{ booking.date }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2 min-w-[210px] relative group-price group">
        <IconClock class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Time</span>
          <span class="text-white">{{ booking.time }}</span>
        </div>
      </div>
    </div>
    <div class="flex w-auto h-full justify-center items-center">
      <button :class="`border-${getColor(booking.status)} text-${getColor(booking.status)}`" class="w-auto h-11 px-4 hover:opacity-90 bg-transparent border rounded-[10px] text-sm font-medium tracking-wide">
       {{getStatus(booking.status)}}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import {IconCalendar, IconClock, IconLeft, IconLike, IconNav, IconRight} from "~/src/shared/ui/common";
import IconAddress from "~/src/shared/ui/common/Icon/IconAddress.vue";

const STATUSES = {
  1: 'Booking Accepted',
  2: 'Booking Declined',
  3: 'Waiting To Accept'
};

const STATUS_COLOR = {
  1: 'green',
  2: 'red',
  3: 'yellow'
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