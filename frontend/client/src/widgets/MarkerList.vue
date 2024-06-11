<script setup>
import { CustomMarker } from 'vue3-google-map';
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {ref} from "vue";

const props = defineProps({
  marker: {
    type: Object,
    required: true,
  },
  logo: {
    type: String,
    required: true,
  },
});

const isDescriptionOpen = ref(false);

const openGoogle = (lat, lng) => {
  const url = `https://www.google.com/maps?q=${lat},${lng}`;
  window.open(url, '_blank');
}
const openDescription = () => {
  isDescriptionOpen.value = !isDescriptionOpen.value;
}
</script>

<template>
  <CustomMarker
      class="flex justify-center items-center relative"
      :options="{ position: { lat: marker.lat, lng:  marker.lng }, anchorPoint: 'CENTER' }">
    <img @click="openDescription" alt="point" class="w-10" src="../shared/assets/image/studio_point.png" />
    <div v-if="isDescriptionOpen" class="p-7 absolute left-12 bg-neutral-900 rounded-[10px] shadow flex-col justify-center items-center gap-7 inline-flex">
      <div  class="w-full h-20">
        <img class="h-20 rounded-[10px] object-fill" src="https://via.placeholder.com/390x210" />
      </div>
      <div class="flex-col w-full justify-start items-start gap-3.5 flex">
        <div @click="openGoogle(marker.lat, marker.lng)" class="text-white text-xl font-bold font-['Montserrat'] uppercase">Studio name</div>
        <div class="text-white text-[14px] font-normal font-['Montserrat'] tracking-wider">Street, no. house, index</div>
        <div class="text-white text-[14px] font-normal font-['Montserrat']">11:00 - 21:00</div>
        <div class="text-white text-[14px] font-normal font-['Montserrat']">100$/hour</div>
      </div>
    </div>
    </CustomMarker>
</template>

<style scoped>

</style>