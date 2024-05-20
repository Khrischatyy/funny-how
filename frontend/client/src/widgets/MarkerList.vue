<script setup>
import { CustomMarker } from 'vue3-google-map';
import {BrandingLogo, BrandingLogoSample, BrandingLogoSmall} from "~/src/shared/ui/branding";

const props = defineProps({
  marker: {
    type: Object,
    required: true,
  }
});

</script>

<template>
  <CustomMarker
      :options="{ position: { lat: marker.lat, lng:  marker.lng }, anchorPoint: 'CENTER' }">
    <BrandingLogoSmall />
  </CustomMarker>
</template>
