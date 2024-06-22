<template>
  <div class="w-full">
    <header class="mt-0 md:mt-10 p-4 w-full flex items-center relative justify-center">
      <button class="lg:hidden p-2 flex items-center absolute left-0" @click="$emit('toggleSideMenu')">
        <IconBurger/>
        <span class="ml-2 text-white">Menu</span>
      </button>
      <div @click="navigateTo('/')" class="flex justify-center cursor-pointer">
        <BrandingLogo />
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
import {IconBurger} from "~/src/shared/ui/common";
import {navigateTo} from "nuxt/app";

const props = withDefaults(defineProps<{
  subhead?: boolean,
  subheadTitle?: string
}>(), {
  subhead: false
});

const emit = defineEmits(['toggleSideMenu']);
</script>
