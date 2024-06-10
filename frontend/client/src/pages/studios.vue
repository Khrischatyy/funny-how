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
import {StudioCard} from "~/src/entities/Studio";


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

const studios = ref([
  { id: 1, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 2, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 3, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 1, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 2, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
  { id: 3, logo: '/logo.png', name: 'Abbey road studios', address: 'Address', hours: '10:00 - 18:00', price: 10 },
]);
</script>

<template>
  <div class="ease-in-out min-h-[100vh] w-full h-full flex flex-col gap-10 items-center justify-center">
      <BrandingLogo/>
  <div class="flex flex-col gap-5 items-center justify-center">
      <FSelect class="font-[BebasNeue]" label="Country" @change="value => {console.log(value)}" :options="['123', '234']"/>
      <FSelect class="font-[BebasNeue]" label="City" @change="value => {console.log(value)}" :options="['123', '234']"/>
      <FSelect class="font-[BebasNeue]" label="Search" @change="value => {console.log(value)}" :options="['123', '234']"/>
  </div>
  <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-10 studio-cards">
    <StudioCard v-for="studio in studios" :studio="studio" :key="studio.id"/>
  </div>
  </div>
</template>

<style scoped lang="scss">

</style>