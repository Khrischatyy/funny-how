<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {definePageMeta, useRuntimeConfig} from '#imports'
import { useSessionStore } from "~/src/entities/Session";
import {  onMounted, ref} from "vue";
import {BrandingLogo} from "~/src/shared/ui/branding";
import {navigateTo} from "nuxt/app";
import {FSelect} from "~/src/entities/RegistrationForms/ui";
import {IconMic} from "~/src/shared/ui/common";
import IconHeadphones from "~/src/shared/ui/common/Icon/IconHeadphones.vue";
import IconMonitor from "~/src/shared/ui/common/Icon/IconMonitor.vue";


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
  <div class="flex flex-col gap-5 items-center justify-center">
      <FSelect class="font-[BebasNeue]" label="Country" @change="value => {console.log(value)}" :options="['123', '234']"/>
      <FSelect class="font-[BebasNeue]" label="City" @change="value => {console.log(value)}" :options="['123', '234']"/>
      <FSelect class="font-[BebasNeue]" label="Search" @change="value => {console.log(value)}" :options="['123', '234']"/>
  </div>
  <div class="flex gap-10 studio-cards">

    <div class="bg-neutral-900 px-7 py-10 rounded-[20px] border-2 border-white border-dashed flex-col justify-start items-center gap-[30px] inline-flex">
      <img class="max-w-[161px] max-h-[161px] object-contain" src="/logo.png" />
      <div class="font-[BebasNeue] text-center text-white text-2xl font-normal">Death Row Records Studio</div>
      <div class="text-center">
        <span class="text-white text-2xl font-normal uppercase font-['BebasNeue']">
        Rating:
        </span>
        <span class="text-red-500 text-2xl font-normal font-['BebasNeue']">5.3</span>
      </div>
      <div class="justify-start items-start gap-[31px] inline-flex">
        <div class="flex-col justify-start items-center gap-[5px] inline-flex">
          <div class="w-[31px] h-[31px] relative flex justify-center">
            <IconMic/>
          </div>
          <div class="text-center text-white text-sm font-normal font-['BebasNeue']">record</div>
        </div>
        <div class="flex-col justify-start items-center gap-[5px] inline-flex">
          <div class="w-[31px] h-[31px] relative">
            <IconMonitor/>
          </div>
          <div class="text-center text-white text-sm font-normal font-['BebasNeue']">rent</div>
        </div>
        <div class="flex-col justify-start items-start gap-[5px] inline-flex">
          <div class="w-[31px] h-[31px] relative">
            <IconHeadphones/>
          </div>
          <div class="text-center text-white text-sm font-normal font-['BebasNeue']">Mixing</div>
        </div>
      </div>
    </div>

  </div>
  </div>
</template>

<style scoped lang="scss">

</style>