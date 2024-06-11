<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {  onMounted, ref} from "vue";
import {BrandingLogo, BrandingLogoSmall} from "~/src/shared/ui/branding";
import {navigateTo} from "nuxt/app";
import {DatePicker} from "~/src/features/DatePicker";


useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

const isLoading = ref(false)


const session = ref()
onMounted(async () => {

})

function signOut() {
  session.value.logout()
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
            Book Studio
          </RouterLink>
        </div>
  </div>
</template>

<style scoped lang="scss">
.font-bebas{
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
        background: #F3F5FD;
        border-radius: 2px;
      }
    }
  }
}

</style>