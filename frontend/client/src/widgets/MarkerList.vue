<script setup>
import { CustomMarker } from 'vue3-google-map';
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";

const props = defineProps({
  marker: {
    type: Object,
    required: true,
  }
});

const openGoogle = (lat, lng) => {
  const url = `https://www.google.com/maps?q=${lat},${lng}`;
  window.open(url, '_blank');
}

</script>

<template>
  <CustomMarker
      :options="{ position: { lat: marker.lat, lng:  marker.lng }, anchorPoint: 'CENTER' }">
    <BrandingLogoSmall @click="openGoogle(marker.lat, marker.lng)" />
  </CustomMarker>
</template>
