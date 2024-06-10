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
  <div class=" container ease-in-out min-h-[100vh] w-full h-full flex flex-col gap-10 items-center justify-center">
    <header class="flex justify-between items-center p-4">
      <BrandingLogo/>
      <button class="flex items-center space-x-2">
        <span class="text-white uppercase bold text-4xl">Sign In</span>
        <img src="./studiosIcons/user 1.svg" alt="Sign In Icon" />
      </button>
    </header>

    <main>
      <button class="flex items-stretch">
        <span class="text-white uppercase bold text-3xl">Pick it in the map</span>
        <img src="./studiosIcons/image 3.svg" alt="Pck it in the map Icon" />
      </button>
      <div class="flex flex-col">
        <form class="border border-white">
          <label for="countries" class="text-white bold capitalize">Country</label>
          <option></option>
        </form>
        <form class="border border-white">
          <label for="cities" class="text-white bold capitalize">City</label>
          <option></option>
        </form>
        <button class="block border border-white">
          <span class="text-white bold capitalize inline">Search</span>
          <img src="./studiosIcons/- 1.png" alt="Search icon" class="inline">
        </button>
      </div>
    </main>

    <footer>

    </footer>
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