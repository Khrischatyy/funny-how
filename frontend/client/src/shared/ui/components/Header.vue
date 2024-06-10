<template>
  <header class="p-4 flex items-center justify-between lg:justify-center">
    <button class="lg:hidden p-2 flex items-center" @click="$emit('toggleSideMenu')">
      <i class="fas fa-bars fa-2x text-white"></i>
      <span class="ml-2">Menu</span>
    </button>
    <div class="flex justify-center">
      <BrandingLogo class="h-12"/>
    </div>
  </header>
</template>

<script setup>
import { defineEmits } from 'vue';
import { BrandingLogo } from '~/src/shared/ui/branding';

const emit = defineEmits(['toggleSideMenu']);
</script>
