<script setup lang="ts">
import { useHead } from '@unhead/vue';
import { ref, onMounted } from 'vue';
import { navigateTo } from 'nuxt/app';
import { useRuntimeConfig } from '#imports';
import { BrandingLogo } from '~/src/shared/ui/branding';

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
});

const isLoading = ref(false);

function redirectToGoogle() {
  window.location.href = '/auth/google/redirect';
}

</script>

<template>
  <div class="ease-in-out min-h-[100vh] w-full h-full flex flex-col gap-10 items-center justify-center">
    <BrandingLogo/>
    <div class="font-bebas flex flex-col text-center gap-5">
      <RouterLink to="/map" class="text-3xl text-white uppercase hover:opacity-70">
        Find Some Here
      </RouterLink>
      <RouterLink to="/studios" class="text-3xl text-white uppercase hover:opacity-70">
        Lock In Your Session
      </RouterLink>
    </div>
    <button
        aria-label="Sign in with Google"
        class="flex items-center gap-3 bg-google-button-dark rounded-full p-0.5 pr-4 transition-colors duration-300 hover:bg-google-button-dark-hover border-dashed border-red"
    >
      <div class="flex items-center justify-center bg-white w-9 h-9 rounded-full">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5">
          <title>Sign in with Google</title>
          <desc>Google G Logo</desc>
          <path
              d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              class="fill-google-logo-blue"
          ></path>
          <path
              d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              class="fill-google-logo-green"
          ></path>
          <path
              d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              class="fill-google-logo-yellow"
          ></path>
          <path
              d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              class="fill-google-logo-red"
          ></path>
        </svg>
      </div>
      <span class="text-sm text-white tracking-wider">Sign in with Google</span>
    </button>
  </div>
</template>

<style scoped lang="scss">
.font-bebas {
  font-family: 'BebasNeue', sans-serif;
}
.checkbox-wrapper {
  display: flex;
  gap: 5px;
  cursor: pointer;

  .custom-checkbox {
    display: inline-block;
    vertical-align: middle;
    position: relative;

    &:after {
      content: '';
      position: absolute;
      display: none;
    }
  }

  input[type="checkbox"] {
    &:checked ~ .custom-checkbox {
      padding: 3px;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;

      &:after {
        position: relative;
        top: 0;
        left: 0;
        display: block;
        width: 100%;
        height: 100%;
        border: solid white;
        background: #171717;
        border-radius: 2px;
      }
    }
  }
}

</style>