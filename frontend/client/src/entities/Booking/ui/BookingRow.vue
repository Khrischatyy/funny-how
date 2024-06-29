<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col sm:flex-row gap-5 justify-between">
    <div class="flex w-auto justify-between items-center">
      <div class="flex justify-start  items-center gap-5">
        <div class="h-[35px] w-[35px]">
          <img :src="booking.address.company.logo_url" alt="Logo" class="h-auto w-full object-cover" />
        </div>
        <div>
          <h3 class="text-xl font-bold text-white">{{  booking.address.company.name }}</h3>
          <p class="font-['Montserrat']">{{booking.userName}}</p>
        </div>
      </div>
    </div>
    <div class="flex w-auto flex-col sm:flex-row gap-8 min-w-[210px] justify-center items-start sm:items-center">
      <div class="flex items-center relative gap-2 ">
        <IconAddress class="opacity-20" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20">Address</span>
          <p class="text-white">{{ booking.address.street }}</p>
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
          <span class="text-white">{{ booking.start_time }} – {{booking.end_time}}</span>
        </div>
      </div>
    </div>
    <div class="flex w-auto h-full justify-center items-center">
      <button :class="`border-${getColor(booking.status.id)} text-${getColor(booking.status.id)}`" class="w-auto h-11 px-4 hover:opacity-90 bg-transparent border rounded-[10px] text-sm font-medium tracking-wide">
       {{getStatus(booking.status.id)}}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import {IconCalendar, IconClock } from "~/src/shared/ui/common";
import IconAddress from "~/src/shared/ui/common/Icon/IconAddress.vue";
import {getStatus, getColor} from "~/src/shared/utils";

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