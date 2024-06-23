<template>
  <div class="py-10">
    <Header />
    <div class="ease-in-out min-h-[100vh] mt-10 w-full h-full flex flex-col gap-10 items-center justify-start">
    <div class="flex flex-col gap-5 items-center justify-center">
      <FSelect
          class="font-[BebasNeue] animate__animated animate__fadeInRight"
          label="Country"
          v-model="selectedCountry"
          @change="handleCountryChange"
          :options="countryOptions"
      />
      <FSelect
          v-if="cityOptions.length > 0"
          class="font-[BebasNeue] animate__animated animate__fadeInRight"
          label="City"
          v-model="selectedCity"
          @change="handleCityChange"
          :options="cityOptions"
      />
      <FInput
          v-if="selectedCity"
          v-model="searchTerm"
          label="Search by name or address"
      />
      <div v-if="selectedCountry || selectedCity || searchTerm" class="relative flex items-center cursor-pointer input border-double">
        <button @click="clearFilters" class="w-full px-3 h-11 flex justify-start items-center outline-none focus:border-white border border-white border-opacity-100 bg-transparent text-white text-sm font-medium tracking-wide">
          Clear Filters
        </button>
      </div>
    </div>
    <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-10 studio-cards">
      <StudioCard class="animate__animated animate__fadeInRight" v-for="studio in filteredStudios" :studio="studio" :key="studio.id" />
    </div>
  </div>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { ref, computed, watch, onMounted, type Ref } from 'vue'
import { FSelect } from '~/src/entities/RegistrationForms/ui'
import { StudioCard } from '~/src/entities/Studio'
import { getStudios, type Studio } from '~/src/entities/RegistrationForms/api/getStudios'
import { useAsyncData } from '#app'
import { useRouter, useRoute } from 'vue-router'
import { getCountries, getCities, type City } from '~/src/entities/RegistrationForms/api'
import {Header} from "~/src/shared/ui/components";
import {FInput} from "~/src/shared/ui/common";

useHead({
  title: 'Funny How – Book a Session Time',
  meta: [
    { name: 'Funny How', content: 'Book A Studio Time' }
  ]
})

type SimpleStudio = {
  id: number
  logo: string
  name: string
  address: string
  hours: string
  price: number
  photos: {
    address_id: number,
    path: string,
    index: number,
    url: string,
  }
  company?: {
    name: string
    address: string
    logo: string
  }
}

const session = ref()
const searchTerm = ref('')
const selectedCountry = ref<string | null>(null)
const selectedCity = ref<string | null>(null)

const { data: countriesData } = await useAsyncData('countries', getCountries)
const countries = ref(countriesData.value ? countriesData.value : [])

const countryOptions = ref(countries.value.map(country => ({ id: country.id, name: country.name })))
const cityOptions: Ref<City[]> = ref([])
const studios: Ref<SimpleStudio[]> = ref([])

const filteredStudios = computed(() => {
  if (!searchTerm.value) {
    return studios.value
  }
  const term = searchTerm.value.toLowerCase()
  return studios.value.filter(studio =>
      studio.name.toLowerCase().includes(term) ||
      studio.address.toLowerCase().includes(term)
  )
})

const handleCountryChange = async (countryId: string) => {
  selectedCountry.value = countryId
  localStorage.setItem('selectedCountry', countryId.toString())
  updateURL()
  const cities = await getCities(countryId)
  cityOptions.value = cities.map(city => ({ id: city.id, name: city.name }))
  if (cityOptions.value.length === 0) {
    selectedCity.value = null // Reset city selection only if there are no cities
    studios.value = [] // Clear studios when there are no cities
  }
}

const handleCityChange = async (cityId: string) => {
  selectedCity.value = cityId;
  localStorage.setItem('selectedCity', cityId.toString());
  updateURL();
  const studiosData = await getStudios(cityId);

  studios.value = studiosData.map(studio => ({
    id: studio.id,
    logo: studio.company.logo,
    name: studio.company.name,
    address: studio.street,
    photos: studio.photos,
    badges: studio.badges,
    prices: studio.prices,
    working_hours: studio.working_hours,
    price: studio.prices.length > 0 ? studio.prices[0].total_price : 0
  }));
}

const handleSearch = () => {
  localStorage.setItem('searchTerm', searchTerm.value)
  updateURL()
}

const updateURL = () => {
  const params = new URLSearchParams()
  if (selectedCountry.value) {
    params.append('country', selectedCountry.value.toString())
  }
  if (selectedCity.value) {
    params.append('city', selectedCity.value.toString())
  }
  if (searchTerm.value) {
    params.append('search', searchTerm.value)
  }
  window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`)
}

const loadFromLocalStorage = () => {
  const savedCountry = localStorage.getItem('selectedCountry')
  const savedCity = localStorage.getItem('selectedCity')
  const savedSearchTerm = localStorage.getItem('searchTerm')
  if (savedCountry) {
    selectedCountry.value = savedCountry
    handleCountryChange(selectedCountry.value)
  }
  if (savedCity) {
    selectedCity.value = savedCity
    handleCityChange(selectedCity.value)
  }
  if (savedSearchTerm) {
    searchTerm.value = savedSearchTerm
  }
}

onMounted(() => {
  loadFromLocalStorage()
})

const clearFilters = () => {
  localStorage.removeItem('selectedCountry')
  localStorage.removeItem('selectedCity')
  localStorage.removeItem('searchTerm')
  selectedCountry.value = null
  selectedCity.value = null
  searchTerm.value = ''
  cityOptions.value = []
  studios.value = []
  updateURL()
}


const route = useRoute()
watch(route, (newRoute) => {
  if (newRoute.query.country) {
    selectedCountry.value = newRoute.query.country as string
    handleCountryChange(selectedCountry.value)
  }
  if (newRoute.query.city) {
    selectedCity.value = newRoute.query.city as string
    handleCityChange(selectedCity.value)
  }
  if (newRoute.query.search) {
    searchTerm.value = newRoute.query.search as string
  }
}, { immediate: true })
</script>

<style scoped lang="scss">
.search-input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 100%;
  max-width: 400px;
  margin: 0.5rem 0;
}
.clear-filters-button {
  padding: 0.5rem 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #f8f8f8;
  cursor: pointer;
  margin-top: 1rem;
}
</style>
