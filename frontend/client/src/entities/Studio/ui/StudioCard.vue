<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col justify-between">
    <div class="flex justify-start items-center mb-4 gap-5">
      <img :src="logoSrc" alt="Logo" class="h-[35px] w-[35px]" />
      <div>
        <h3 class="text-xl font-bold text-white">{{ studio.name }}</h3>
        <p class="text-white">{{ studio.address }}</p>
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center relative">
      <div v-for="(photo, index) in displayedPhotos" :key="index" class="w-24 h-20 relative">
        <div v-if="index === 0" @click="prevPhoto" class="cursor-pointer w-24 h-20 bg-gradient-to-r from-[#1a1a1a] to-transparent rounded-lg absolute flex items-center justify-start">
          <IconLeft iconType="thin" />
        </div>
        <div v-if="index === displayedPhotos.length - 1" @click="nextPhoto" class="cursor-pointer w-24 h-20 bg-gradient-to-r from-transparent to-[#1a1a1a] rounded-lg absolute flex items-center justify-end">
          <IconRight iconType="thin" />
        </div>
        <img :src="photo.url" alt="Photo" class="w-full h-full object-contain rounded-[10px]" />
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center">
      <div v-if="studio.badges.length > 5" class="relative">
        <div class="flex overflow-x-scroll">
          <img v-for="(badge, index) in studio.badges" :key="badge.id" :src="badge.image" :alt="badge.name" class="w-8 h-8 object-contain mr-2" />
        </div>
        <div class="absolute top-0 left-0 h-full w-8 bg-gradient-to-r from-[#1a1a1a] to-transparent"></div>
        <div class="absolute top-0 right-0 h-full w-8 bg-gradient-to-r from-transparent to-[#1a1a1a]"></div>
      </div>
      <div v-else>
        <img v-for="(badge, index) in studio.badges" :key="badge.id" :src="badge.image" :alt="badge.name" class="w-8 h-8 object-contain mr-2" />
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center">
      <div class="flex items-center relative group">
        <IconClock class="opacity-20 group-hover:opacity-100" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20 group-hover:opacity-100">Working Hours</span>
          <span class="text-white">{{ todayWorkingHours }}</span>
        </div>
        <div class="absolute bottom-full right-0 mb-2 p-2 bg-gray-800 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
          <ul>
            <li v-for="hour in studio.working_hours" :key="hour.id">
              {{ daysOfWeek[hour.day_of_week] }}: {{ hour.open_time }} - {{ hour.close_time }}
            </li>
          </ul>
        </div>
      </div>
      <div class="flex items-center gap-2 relative group">
        <IconPrice class="opacity-20 group-hover:opacity-100" />
        <div class="flex flex-col">
          <span class="text-white opacity-20 group-hover:opacity-100">Price</span>
          <span class="text-white">{{ primaryPrice }}</span>
        </div>
        <div class="absolute bottom-full right-0 mb-2 p-2 bg-gray-800 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
          <ul>
            <li v-for="price in studio.prices" :key="price.id">
              ${{ price.total_price }} / {{ price.hours }} hour
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import IconPrice from "~/src/shared/ui/common/Icon/Filter/IconPrice.vue";
import { IconClock, IconLeft, IconRight } from "~/src/shared/ui/common";

// Import the default images
import defaultLogo from '~/src/shared/assets/image/studio.png';

import defaultPhoto3_1 from '~/src/shared/assets/image/skeleton-studio-card/music-studio.png';
import defaultPhoto3_2 from '~/src/shared/assets/image/skeleton-studio-card/studio.png';
import defaultPhoto3_3 from '~/src/shared/assets/image/skeleton-studio-card/studio-microphone.png';

const props = defineProps({
  studio: {
    type: Object,
    required: true
  }
});

const currentIndex = ref(0);
const showPriceTooltip = ref(false);

const logoSrc = computed(() => {
  return props.studio.logo && props.studio.logo.trim() !== '' ? props.studio.logo : defaultLogo;
});

const displayedPhotos = computed(() => {
  let photos = props.studio.photos ? props.studio.photos.slice(currentIndex.value, currentIndex.value + 3) : [];

  while (photos.length < 3) {
    if (photos.length === 0) {
      photos.push({ id: 'default1', url: defaultPhoto3_1 });
    } else if (photos.length === 1) {
      photos.push({ id: 'default2', url: defaultPhoto3_2 });
    } else if (photos.length === 2) {
      photos.push({ id: 'default3', url: defaultPhoto3_3 });
    }
  }

  return photos.slice(0, 3);
});

const nextPhoto = () => {
  if (currentIndex.value + 3 < (props.studio.photos ? props.studio.photos.length : 0)) {
    currentIndex.value += 1;
  }
};

const prevPhoto = () => {
  if (currentIndex.value > 0) {
    currentIndex.value -= 1;
  }
};

// Days of the week in English
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const todayWorkingHours = computed(() => {
  const today = new Date().getDay();
  const todayHours = props.studio.working_hours.find(hour => hour.day_of_week === today);
  if (!todayHours) return 'Closed';
  if (todayHours.mode_id === 1) {
    return '24h';
  } else {
    return `${todayHours.open_time} - ${todayHours.close_time}`;
  }
});

const primaryPrice = computed(() => {
  if (props.studio.prices && props.studio.prices.length > 0) {
    const price = props.studio.prices[0];
    return `$${price.total_price} / ${price.hours} hour`;
  }
  return '';
});
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1 !important;
}
</style>