<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {  onMounted, ref} from "vue";
import {BrandingLogo} from "~/src/shared/ui/branding";
import {navigateTo} from "nuxt/app";
import {IconDown} from "~/src/shared/ui/common";


useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

const isLoading = ref(false)


const session = ref()
onMounted(async () => {
  const config = useRuntimeConfig()
  session.value = useSessionStore()
  if (session.value.brand) {
    navigateTo(`/@${session.value.brand}`)
  }

})

function signOut() {
  session.value.logout()
}

</script>

<template>
  <div class="ease-in-out min-h-[100vh] w-full h-full flex flex-col gap-10 items-center justify-center">
      <BrandingLogo/>
    <div class="w-96">
      <div class="input border-double relative">
        <input type="text" class="border-double outline-0 w-full h-full relative focus:border-white border border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide"/>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.border-double{
  min-height: 61px;
}
.border-double::before{
  content: '';
  position: absolute;
  bottom: 3px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}
.border-double::after{
  content: '';
  position: absolute;
  bottom: 6px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}
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