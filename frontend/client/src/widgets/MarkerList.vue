<script setup>
import { CustomMarker } from 'vue3-google-map';
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";

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

const openGoogle = (lat, lng) => {
  const url = `https://www.google.com/maps?q=${lat},${lng}`;
  window.open(url, '_blank');
}

</script>

<template>
  <CustomMarker
      :options="{ position: { lat: marker.lat, lng:  marker.lng }, anchorPoint: 'CENTER' }">
    <img @click="openGoogle(marker.lat, marker.lng)" :src="props.logo" alt="logo" class="w-10 h-10 rounded-full border-2 border-white bg-white shadow-lg -mt-5 -ml-5" />
  </CustomMarker>
</template>
