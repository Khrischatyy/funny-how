<template>
  <div>
    <header class="p-4 flex items-center justify-between lg:justify-center">
      <button class="lg:hidden p-2 flex items-center" @click="$emit('toggleSideMenu')">
        <i class="fas fa-bars fa-2x text-white"></i>
        <span class="ml-2">Menu</span>
      </button>
      <div class="flex justify-center">
        <BrandingLogo class="h-12"/>
      </div>
    </header>
    <div v-if="subhead" class="subhead flex flex-1 mb-4">
      <div class="subhead__back lg:w-64">
      </div>
      <div class="subhead__title px-2 md:px-4">
        <h1 class="text-3xl font-bold">{{ subheadTitle }}</h1>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineEmits } from 'vue';
import { BrandingLogo } from '~/src/shared/ui/branding';

const props = withDefaults(defineProps<{
  subhead?: boolean,
  subheadTitle?: string
}>(), {
  subhead: false
});

const emit = defineEmits(['toggleSideMenu']);
</script>
