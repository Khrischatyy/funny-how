<script setup>
import {GoogleMap, MarkerCluster} from 'vue3-google-map';
import {ref, computed, onMounted} from 'vue';
import MarkerList from "~/src/widgets/MarkerList.vue";
import { useRuntimeConfig } from '#imports'; // Ensure correct import
const config = useRuntimeConfig(); // Ensure correct usage


const props = defineProps({
  lat: {
    type: String
  },
  lng: {
    type: String
  },
  logo: {
    type: String
  },
  title: {
    type: String
  }
});

const defaultLat = 48.21888549557031;
const defaultLng = 11.625109549171704;

const center = computed(() => ({
  lat: props.lat ? parseFloat(props.lat) : defaultLat,
  lng: props.lng ? parseFloat(props.lng) : defaultLng
}));

const zoom = ref(15);

const studios = {
  studio1: {
    id: 'studio1',
    name: 'FC Bayern Studio',
    description: 'A top-notch studio used by FC Bayern München. Perfect for high-quality recordings and events.',
    lat: center.value.lat,
    lng: center.value.lng
  }
};

</script>

<template>
  <ClientOnly>
    {{config.public.googleMapKey}}
    <GoogleMap :api-key="config.public.googleMapKey" class="map" :center="center" :zoom="zoom">
      <MarkerCluster :options="{ position: center }">
        <MarkerList v-for="studio in studios" :key="studio.id" :logo="props.logo || '/logo.png'" :marker="studio"/>
      </MarkerCluster>
    </GoogleMap>
  </ClientOnly>
</template>

<style scoped>
.map {
  width: 100%;
  height: 100%;
}
</style>
