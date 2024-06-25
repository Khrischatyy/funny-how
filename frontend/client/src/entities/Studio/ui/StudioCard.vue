<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col justify-between max-w-[380px]">
    <div class="flex justify-between items-center mb-4">
    <div class="flex justify-start items-center gap-5">
      <img :src="logoSrc" alt="Logo" class="h-[35px] w-[35px]" />
      <div>
        <h3 class="text-xl font-bold text-white">{{ studio.name }}</h3>
        <p class="text-white">{{ studio.address }}</p>
      </div>
    </div>
    <div v-if="isDelete" class="flex items-center gap-3 cursor-pointer hover:opacity-70">
      <IconTrash/>
    </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center relative">
      <PhotoSwipe v-if="displayedPhotos" :photos="displayedPhotos" ref="photoSwipe"/>
    </div>

    <div class="mt-4 flex gap-3 w-full justify-center items-center relative mb-5">
      <ScrollContainer>
        <div v-for="(badge, index) in displayedBadges" :key="badge.id" @mouseenter="showTooltip($event, badge.description)" @mouseleave="hideTooltip" class="relative w-8 h-full group scrollElement">
          <Component :is="ICON_MAP[badge.name as typeof ICON_MAP]" class="w-full h-8 object-fit group-hover:opacity-70" />
<!--          <img :src="'https://via.placeholder.com/32x32' || badge.image" :alt="badge.name" class="w-full h-full object-contain" />-->
          <div class="text-white text-xs text-center mt-1 group-hover:opacity-70 font-[BebasNeue]">{{ badge.name }}</div>
        </div>
      </ScrollContainer>
    </div>
    <div class="mt-4 flex gap-3 justify-between items-center">
      <div @mouseenter="showTooltip($event, generateTooltipContent('hours'))" @mouseleave="hideTooltip" class="flex items-center relative group-hours-block group">
        <IconClock class="opacity-20 group-hover:opacity-100" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20 group-hover:opacity-100">Working Hours</span>
          <span class="text-white">{{ todayWorkingHours }}</span>
        </div>
      </div>

      <div @mouseenter="showTooltip($event, generateTooltipContent('price'))" @mouseleave="hideTooltip" class="flex items-center gap-2 relative group-price group">
        <IconPrice class="opacity-20 group-hover:opacity-100" />
        <div class="flex flex-col group-hover:opacity-100">
          <span class="text-white opacity-20 group-hover:opacity-100">Price</span>
          <span class="text-white">{{ primaryPrice }}</span>
        </div>
      </div>
    </div>

    <Tooltip>
      {{ tooltipData.content }}
    </Tooltip>
  </div>
</template>

<script setup lang="ts">
import {computed, inject, ref} from 'vue';
import IconPrice from "~/src/shared/ui/common/Icon/Filter/IconPrice.vue";
import {
  IconBooking,
  IconClock,
  IconLeft,
  IconLike,
  IconMic,
  IconMonitor,
  IconRight,
  IconTrash
} from "~/src/shared/ui/common";

// Import the default images
import defaultLogo from '~/src/shared/assets/image/studio.png';

import defaultPhoto3_1 from '~/src/shared/assets/image/skeleton-studio-card/music-studio.png';
import defaultPhoto3_2 from '~/src/shared/assets/image/skeleton-studio-card/studio.png';
import defaultPhoto3_3 from '~/src/shared/assets/image/skeleton-studio-card/studio-microphone.png';
import {ScrollContainer} from "~/src/shared/ui/common/ScrollContainer";
import {Tooltip} from "~/src/shared/ui/Tooltip";
import {PhotoSwipe} from "~/src/shared/ui/components/PhotoSwipe";

const props = defineProps({
  studio: {
    type: Object,
    required: true
  },
  isDelete: {
    type: Boolean,
    default: false
  }
});

const ICON_MAP = {
  'mixing': IconMonitor,
  'record': IconMic,
  'rent': IconBooking,
};

const { tooltipData, showTooltip, hideTooltip } = inject('tooltipData');

const currentIndex = ref(0);
const badgeIndex = ref(0);

const logoSrc = computed(() => {
  return props.studio.logo && props.studio.logo.trim() !== '' ? props.studio.logo : defaultLogo;
});

const displayedPhotos = computed(() => {
  let defaultPhotos = [
    { url: defaultPhoto3_1 },
    { url: defaultPhoto3_2 },
    { url: defaultPhoto3_3 }
  ];
  let yelpPhotos = [
    {url: 'https://s3-media0.fl.yelpcdn.com/bphoto/3rzbCcdBlgmmHOwwsaWB5g/o.jpg'},
    {url: 'https://s3-media0.fl.yelpcdn.com/bphoto/zGA_lgsYRGXHofIHsPSs1g/o.jpg'},
    {url: 'https://s3-media0.fl.yelpcdn.com/bphoto/h9D3QpnWeubUlwucVv_2Fg/o.jpg'},
    {url: 'https://s3-media0.fl.yelpcdn.com/bphoto/vFWvXrnWdaRaFrACieFqpQ/o.jpg'},
  ]

  let photos = props.studio.photos ? props.studio.photos.slice(currentIndex.value, currentIndex.value + 3) : [];
  if (photos.length < 3) {
    photos = photos.concat(defaultPhotos);
  }
  return yelpPhotos;
  return photos.slice(0, 4);
});

const displayedBadges = computed(() => {
  return props.studio.badges.slice(badgeIndex.value, 10);
});

// Days of the week in English
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function generateTooltipContent(type) {
  if(type === 'hours') {
    let hours = props.studio.operating_hours;
    // Sort the hours by day of the week
    hours.sort((a, b) => a.day_of_week - b.day_of_week);

    // Map over the sorted hours to create formatted strings
    return hours.map(hour => {
      const dayName = daysOfWeek[hour.day_of_week];
      const timeString = hour.is_closed ? "Closed" : `${hour.open_time.substring(0, 5)} - ${hour.close_time.substring(0, 5)}`;
      return `${dayName}: ${timeString}`;
    }).join('\n');
  } else if(type === 'price') {

    return props.studio.prices.map(price => {
      return `${price.total_price} / ${price.hours} hour`;
    }).join('\n');
  }

}

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

const nextBadge = () => {
  if (badgeIndex.value + 5 < props.studio.badges.length) {
    badgeIndex.value += 1;
  }
};

const prevBadge = () => {
  if (badgeIndex.value > 0) {
    badgeIndex.value -= 1;
  }
};


const todayWorkingHours = computed(() => {
  const today = new Date().getDay();
  return '24h';
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

.group-price:hover .group-hover:opacity-100 {
  opacity: 1 !important;
}

.group-hours-block:hover .group-hover:opacity-100 {
  opacity: 1 !important;
}


</style>