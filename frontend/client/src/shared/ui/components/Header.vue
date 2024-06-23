<template>
  <div class="w-full">
    <header class="mt-0 md:mt-10 mx-auto p-4 w-full md:max-w-3xl flex items-center relative justify-center">
      <button v-if="sideMenuArray.length > 0" class="lg:hidden p-2 flex items-center absolute left-0" @click="$emit('toggleSideMenu')">
        <IconBurger/>
        <span class="ml-2 text-white">Menu</span>
      </button>
      <div @click="navigateTo('/')" class="flex justify-center cursor-pointer">
        <BrandingLogo />
      </div>
      <button v-if="!isAuth && !hideLoginButton" class="p-2 flex items-center hover:opacity-70 absolute right-0" @click="navigateTo('/login')">
        <span class="hidden min-[300px]:block mr-2 text-white text-2xl font-[BebasNeue]">Sign In</span>
        <IconUser class="border-[1.5px] border-white rounded-full w-6 h-6"/>
      </button>
      <button v-if="isAuth && !hideLoginButton" class="p-2 flex items-center hover:opacity-70 absolute right-0" @click="$emit('toggleSideMenu')">
        <IconUser class="border-[1.5px] border-white rounded-full w-8 h-8"/>
      </button>
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
import {defineEmits, ref} from 'vue';
import { BrandingLogo } from '~/src/shared/ui/branding';
import {IconBurger, IconUser} from "~/src/shared/ui/common";
import {navigateTo} from "nuxt/app";
import {useCookie} from "#app";
import {ACCESS_TOKEN_KEY} from "~/src/entities/Session";

const props = withDefaults(defineProps<{
  subhead?: boolean,
  subheadTitle?: string,
  hideLoginButton?: boolean
}>(), {
  subhead: false
});
const sideMenuArray = ref([]);
const isAuth = useCookie(ACCESS_TOKEN_KEY).value;
const emit = defineEmits(['toggleSideMenu']);
</script>
