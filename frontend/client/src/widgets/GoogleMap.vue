<script setup>
import {GoogleMap, MarkerCluster} from 'vue3-google-map';
import {ref, computed, onMounted, watch, reactive} from 'vue';
import MarkerList from "~/src/widgets/MarkerList.vue";
import {useRuntimeConfig} from '#imports';

const config = useRuntimeConfig();

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
  },
  markers: {
    type: Array
  }
});

const defaultLat = 48.21888549557031;
const defaultLng = 11.625109549171704;

const center = computed(() => ({
  lat: props.lat ? parseFloat(props.lat) : defaultLat,
  lng: props.lng ? parseFloat(props.lng) : defaultLng
}));

const zoom = ref(15);
const studios = ref([]); // Using ref instead of reactive
const updateStudios = (markers) => {
  studios.value = markers.map((studio) => ({
    id: studio.id,
    name: studio.company.name,
    lat: parseFloat(studio.latitude),
    lng: parseFloat(studio.longitude),
    street: studio.street,
    price: studio.prices.length > 0 ? studio.prices[0].total_price : 'N/A',
    operatingHours: studio.operating_hours.length > 0 ? `${studio.operating_hours[0].open_time} - ${studio.operating_hours[0].close_time}` : 'N/A',
    photos: studio.photos.length > 0 ? studio.photos[0].url : '',
    url: `/@${studio.company.slug}/studio/${studio.id}`,
  }));
  console.log('Updated studios:', studios.value);
};

onMounted(() => {
  if (props.markers) {
    updateStudios(props.markers);
  }
});

watch(() => props.markers, (newMarkers) => {
  if (newMarkers) {
    updateStudios(newMarkers);
  }
}, {immediate: true}); // Add immediate to run the watch initially



</script>

<template>
  <ClientOnly>
    <GoogleMap v-if="studios.length > 0" :api-key="config.public.googleMapKey" class="map" :center="center"
               :zoom="zoom">
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