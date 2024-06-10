<script setup lang="ts">
import {useHead} from "@unhead/vue";
import {onMounted, ref} from "vue";
import {BrandingLogo} from "~/src/shared/ui/branding";
import {FSelect} from "~/src/entities/RegistrationForms/ui";
import {StudioCard} from "~/src/entities/Studio";
import {getCountries} from "~/src/entities/RegistrationForms/api";
import {useAsyncData} from "#app";


useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ],
})

const session = ref()

const { data: countriesData } = await useAsyncData('countries', getCountries)
const countries = ref(countriesData.value ? countriesData.value : [])
const countryNames = ref(countries.value.map(country => country.name))


onMounted(() => {
  console.log('countriesData', countriesData.value)
      getCountries().then(response => {
        console.log(response)
      })
    }
)

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
      <FSelect class="font-[BebasNeue]" label="Country" @change="value => {console.log(value)}" :options="countries"/>
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