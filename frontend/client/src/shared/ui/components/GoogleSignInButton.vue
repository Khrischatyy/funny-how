<template>
  <button
      v-if="!isAuthorized"
      aria-label="Sign in with Google"
      @click="redirectToGoogle"
      class="flex items-center gap-3 rounded-full font-bebas p-0.5 pr-4 transition-colors duration-300 hover:bg-google-button-dark-hover border-dashed border-red"
  >
    <div class="flex items-center justify-center bg-white w-9 h-9 rounded-full ">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5">
        <title>Sign in with Google</title>
        <desc>Google G Logo</desc>
        <path
            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
            class="text-black"
        ></path>
        <path
            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
            class="text-black"
        ></path>
        <path
            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
            class="text-black"
        ></path>
        <path
            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
            class="text-black"
        ></path>
      </svg>
    </div>
    <span class="text-sm text-white tracking-wider font-[BebasNeue]">Sign in with Google</span>
  </button>
</template>

<script setup lang="ts">
import { useRuntimeConfig } from '#imports';
import {useCookie} from "#app";
import {ACCESS_TOKEN_KEY} from "~/src/lib/api/config";

const isAuthorized = useCookie(ACCESS_TOKEN_KEY).value;

function redirectToGoogle() {
  window.location.href = `/api/v1/auth/google/redirect`;
}
</script>

<style scoped>
.font-bebas {
  font-family: 'BebasNeue', sans-serif;
}
</style>