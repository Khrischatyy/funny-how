<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col justify-between">
    <div class="flex justify-start items-center mb-4 gap-5">
      <img :src="studio.logo" alt="Logo" class="h-12 w-12 object-contain rounded-full" />
      <div>
        <h3 class="text-xl font-bold text-white">{{ studio.name }}</h3>
        <p class="text-white">{{ studio.address }}</p>
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center">
      <div v-for="(photo, index) in studio.photos" :key="photo.id" class="w-24 h-20 relative">
        <div v-if="index == 0" class="cursor-pointer w-24 h-20 bg-gradient-to-r from-[#1a1a1a] to-transparent rounded-lg rounded-[8px] absolute flex items-center justify-start">
          <IconLeft iconType="thin"/>
        </div>
        <div v-if="index == studio.photos.length - 1" class="cursor-pointer w-24 h-20 bg-gradient-to-r from-transparent to-[#1a1a1a] rounded-lg rounded-[8px] absolute flex items-center justify-end">
          <IconRight iconType="thin"/>
        </div>
        <img :src="photo.url" alt="Photo" class="w-full h-full object-contain rounded-[10px]" />
      </div>
    </div>

    <div class="mt-4 flex gap-3 justify-between items-center">
      <div class="flex items-center">
        <IconClock class="opacity-20"/>
        <div class="flex flex-col">
          <span class="text-white opacity-20">Working Hours</span>
          <span class="text-white">{{ studio.hours }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <IconPrice class="opacity-20"/>
        <div class="flex flex-col">
          <span class="text-white opacity-20">Price</span>
          <span class="text-white">{{ studio.price }}$ / hour</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import IconPrice from "~/src/shared/ui/common/Icon/Filter/IconPrice.vue";
import {IconClock, IconLeft, IconRight} from "~/src/shared/ui/common";

defineProps({
  studio: {
    type: Object,
    required: true
  }
});
</script>

<style scoped>
.card {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.card img {
  border-radius: 50%;
}
.card h3 {
  font-size: 1.25rem;
  font-weight: bold;
}
.card p {
  color: #9ca3af;
}
.card .details {
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card .details i {
  color: #9ca3af;
}
.card .details span {
  color: #9ca3af;
}
</style>
