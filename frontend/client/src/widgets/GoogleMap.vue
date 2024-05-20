<script setup>
import { GoogleMap, MarkerCluster } from 'vue3-google-map';
import {ref, watch} from "vue";
import MarkerList from "~/src/widgets/MarkerList.vue";

const zoom = ref(15);
const props = defineProps({
  lat: {
    type: String
  },
  lng: {
    type: String
  }
});

const locations = {
  FCB: {
    id: 'FCB',
    headline: 'FC Bayern München',
    text: 'Bayern München is the most successful soccer club in Germany. In the german Bundesliga their current rank is #1. Add some more text for this item to make it look bigger',
    lat: props.lat ? parseFloat(props.lat) : 48.21888549557031,
    lng: props.lng ? parseFloat(props.lng) : 11.625109549171704
  },
};

const center = {
  lat: props.lat ? parseFloat(props.lat) : 48.21888549557031,
  lng: props.lng ? parseFloat(props.lng) : 11.625109549171704
};
function zoomEvent(item) {
  center.value = { lat: item.lat, lng: item.lng };
  zoom.value = 25;
}
</script>
<template>
  <ClientOnly>
    <GoogleMap  api-key="AIzaSyBQyFCU8EovilnLJEi2vTs623u8ftgMigY" class="map w-full" :center="center" :zoom="zoom">
      <MarkerCluster :options="{ position: center }">
        <div v-for="marker in locations">
          <MarkerList :marker="marker" />
        </div>
      </MarkerCluster>
    </GoogleMap>
  </ClientOnly>
</template>

<style scoped>
.map {
  position: relative;
  width: 100%;
  height: 300px;
}
</style>
