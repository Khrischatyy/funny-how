<script setup lang="ts">
import { useHead } from '@unhead/vue';
import {ref, onMounted, onBeforeMount, computed, watch} from 'vue';
import { useRuntimeConfig } from '#imports';
import { BrandingLogo } from '~/src/shared/ui/branding';
import {ACCESS_TOKEN_KEY, useSessionStore} from "~/src/entities/Session";
import {useCookie} from "#app";
import GoogleSignInButton from '~/src/shared/ui/components/GoogleSignInButton.vue';
import {Header, Footer} from "~/src/shared/ui/components";
import {navigateTo} from "nuxt/app";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
});

const isLoading = ref(false);
const isBrand = ref('');
const session = useSessionStore();

watch(() => session.brand, (newValue) => {
  isBrand.value = <string>newValue;
});

onMounted(() => {
  isBrand.value = <string>session.brand;
});

function redirectToGoogle() {
  const config = useRuntimeConfig();
  window.location.href = `/api/v1/auth/google/redirect`;
}
const isShowLogin = computed(() => {
  return !useCookie(ACCESS_TOKEN_KEY).value;
});
function signOut() {
  useSessionStore().logout();
}
</script>

<template>
  <div class="ease-in-out min-h-[100vh] w-full h-full flex flex-col gap-10 items-center justify-between">
    <Header />
    <div class="h-full min-h-[50vh] w-full justify-center items-center flex flex-col gap-2">
      <div class="font-[BebasNeue] flex flex-col text-center gap-5">
        <RouterLink to="/map" class="text-4xl text-white uppercase hover:opacity-70">
          <div class="flex gap-3 justify-center items-center ">
            <div>
              Find Some Here
            </div>
            <img
                class="h-[25px] relative -translate-y-[2px]"
                src="../shared/assets/image/map.svg"
                alt="Funny How"
            >
          </div>
        </RouterLink>
        <RouterLink to="/studios" class="text-4xl text-white uppercase hover:opacity-70 flex justify-center">
          <div class="">
            Lock In Your Session
          </div>
          <div class="flex items-center justify-center relative -translate-y-1 translate-x-2">
            <img
                class="h-[25px]"
                src="../shared/assets/image/lockin.svg"
                alt="Funny How"
            >
          </div>
        </RouterLink>
      </div>
      <GoogleSignInButton class="mt-10" />
      <div v-if="isBrand" class="justify-center w-full max-w-96 p-5 items-center gap-2.5 inline-flex mt-10">
        <button @click="navigateTo(`/@${isBrand}`)" class="w-full h-11 p-3.5 hover:opacity-90 border border-white rounded-[10px] text-white text-sm font-medium tracking-wide">
          My company @{{ isBrand }}
        </button>
      </div>
      <div v-if="useCookie(ACCESS_TOKEN_KEY).value" class="justify-center w-full max-w-96 p-5 items-center gap-2.5 inline-flex mt-10">
        <button @click="signOut()" class="w-full h-11 p-3.5 hover:opacity-90 border border-white rounded-[10px] text-white text-sm font-medium tracking-wide">Sign Out</button>
      </div>
    </div>
    <Footer />
  </div>
</template>

<style scoped lang="scss">
.font-bebas {
  font-family: 'BebasNeue', sans-serif;
}
</style>