<template>
  <div class="bg-custom-gray text-white rounded-lg p-4 relative">
    <div class="flex justify-between items-center mb-2">
      <img :src="studio.logo" alt="Logo" class="w-12 h-12">
      <button class="text-red-500 absolute top-2 right-2">
        <i class="fas fa-trash-alt"></i>
      </button>
    </div>
    <h3 class="text-lg font-bold">{{ studio.name }}</h3>
    <p class="text-sm">{{ studio.address }}</p>
    <div class="flex justify-between items-center mt-2">
      <p class="flex items-center"><i class="fas fa-clock mr-2"></i>{{ studio.hours }}</p>
      <p class="flex items-center"><i class="fas fa-dollar-sign mr-2"></i>{{ studio.price }}$ / hour</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps<{
  studio: {
    id: number;
    logo: string;
    name: string;
    address: string;
    hours: string;
    price: number;
  };
}>();
</script>
