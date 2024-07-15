<template>
  <Suspense>
    <div class="py-0 px-5 pd-0 md:pt-5 bg-black">
      <Header logo-size="large" />

      <div
        class="ease-in-out min-h-screen h-full max-w-7xl m-auto mt-10 w-full flex flex-col gap-10 items-center justify-start"
        style="min-height: -webkit-fill-available"
      >
        <FilterBar
          :filters-show="filterShow"
          @update:filters="handleFiltersChange"
        />
        <div
          class="flex flex-col gap-5 w-full max-w-72 items-center justify-center"
        >
          <div class="font-[BebasNeue] flex flex-col text-center gap-5">
            <RouterLink
              to="/map"
              class="text-4xl text-white uppercase hover:opacity-70"
            >
              <div class="flex gap-3 justify-center items-center">
                <div class="font-['BebasNeue']">Pick on the map</div>
                <img
                  class="h-[30px] relative -translate-y-[2px]"
                  src="../shared/assets/image/map.svg"
                  alt="Funny How"
                />
              </div>
            </RouterLink>
          </div>
          Selected: {{ selectedCountry }}
          <FSelect
            class="font-[BebasNeue] z-50 animate__animated animate__fadeInRight"
            placeholder="Country"
            model-key="id"
            size="sm"
            v-model="selectedCountry"
            @change="handleCountryChange"
            :options="countryOptions"
          />
          <FSelect
            v-if="cityOptions.length > 0"
            class="font-[BebasNeue] z-40 animate__animated animate__fadeInRight"
            placeholder="City"
            model-key="id"
            size="sm"
            v-model="selectedCity"
            @change="handleCityChange"
            :options="cityOptions"
          />
          <div
            v-if="filteredStudios.length > 0"
            class="text-white text-4xl leading-1 mt-3 text-center font-[BebasNeue]"
          >
            Found studios <DisplayNumber :value="filteredStudios.length" />
            <br />
            in
            {{ cityOptions.find((city) => city.id == selectedCity)?.name }}:
          </div>
          <div
            v-if="
              (selectedCountry || selectedCity || searchTerm) &&
              cityOptions.length > 100000000000
            "
            class="relative flex items-center cursor-pointer input border border-white border-double"
          >
            <button
              @click="clearFilters"
              class="w-full px-3 h-11 flex justify-start items-center outline-none bg-transparent text-white text-sm font-medium tracking-wide"
            >
              Clear Filters
            </button>
          </div>
        </div>
        <div :class="`grid ${gridColumns} gap-10 studio-cards mb-10`">
          <div
            class="animate__animated animate__fadeInRight"
            :class="centeredClass"
            v-for="studio in filteredStudios"
            :key="studio.id"
            @click="goToStudio(studio)"
          >
            <StudioCard
              :class="`border border-white border-opacity-50 hover:border-opacity-100 max-w-[22rem] cursor-pointer zoom-effect`"
              :studio="studio"
            />
          </div>
        </div>
      </div>
    </div>
  </Suspense>
</template>

<script setup lang="ts">
import { useHead } from "@unhead/vue"
import { ref, computed, watch, onMounted, type Ref, reactive } from "vue"
import { DisplayNumber, FilterBar } from "~/src/shared/ui/components"
// import { FSelect } from '~/src/entities/RegistrationForms/ui'
import { FSelect } from "~/src/shared/ui/common"
import { StudioCard } from "~/src/entities/Studio"
import {
  getStudios,
  type Studio,
} from "~/src/entities/RegistrationForms/api/getStudios"
import { useAsyncData } from "#app"
import { useRouter, useRoute } from "vue-router"
import {
  getCountries,
  getCities,
  type City,
} from "~/src/entities/RegistrationForms/api"
import { Header } from "~/src/shared/ui/components"
import { FInput } from "~/src/shared/ui/common"
import { navigateTo } from "nuxt/app"

useHead({
  title: "Funny How – Book a Session Time",
  meta: [{ name: "Funny How", content: "Book A Studio Time" }],
})

type SimpleStudio = {
  id: number
  logo: string
  name: string
  street: string
  hours: string
  price: number
  photos: {
    address_id: number
    path: string
    index: number
    url: string
  }
  company?: {
    name: string
    address: string
    logo: string
    slug: string
  }
}

const session = ref()
const searchTerm = ref("")
const selectedCountry = ref<string | null>(null)
const selectedCity = ref<string | null>(null)

const { data: countriesData } = await useAsyncData("countries", getCountries)
const countries = ref(countriesData.value ? countriesData.value : [])

const countryOptions = ref(
  countries.value.map((country) => ({
    id: country.id,
    name: country.name,
    label: country.name.toUpperCase(),
  })),
)
const cityOptions: Ref<City[]> = ref([])
const studios: Ref<SimpleStudio[]> = ref([])

const filteredStudios = computed(() => {
  if (!searchTerm.value) {
    return studios.value
  }
  const term = searchTerm.value.toLowerCase()
  return studios.value.filter(
    (studio) =>
      studio.company?.name.toLowerCase().includes(term) ||
      studio.street.toLowerCase().includes(term),
  )
})

const gridColumns = computed(() => {
  if (filteredStudios.value.length === 1) {
    return "md:grid-cols-3 sm:grid-cols-1"
  } else if (filteredStudios.value.length === 2) {
    return "md:grid-cols-2 sm:grid-cols-1"
  } else {
    return "md:grid-cols-3 sm:grid-cols-1"
  }
})

const centeredClass = computed(() => {
  if (filteredStudios.value.length === 1) {
    return "md:col-start-2"
  }
  return ""
})

const goToStudio = (studio) => {
  navigateTo(`/@${studio?.slug}`)
}

const handleFiltersChange = (newFilters) => {
  searchTerm.value = newFilters.search

  if (newFilters.city) {
    selectedCity.value = newFilters.city
    handleCityChange(selectedCity.value)
  }
  if (newFilters.price) {
    // handlePriceChange(newFilters.price)
  }
  if (newFilters.rating) {
    // handleRatingChange(newFilters.rating)
  }
}

const filterShow = reactive([
  { key: "search", options: "", value: "" },
  {
    key: "badges",
    options: [
      { id: 1, name: "Recoring" },
      { id: 2, name: "Rent" },
    ],
    value: "",
  },
  {
    key: "price",
    options: [],
    value: "",
  },
  {
    key: "rating",
    options: [],
    value: "",
  },
])

const handleCountryChange = async (countryId: string) => {
  selectedCountry.value = countryId
  localStorage.setItem("selectedCountry", countryId.toString())
  updateURL()
  const cities = await getCities(countryId)
  cityOptions.value = cities.map((city) => ({
    id: city.id,
    name: city.name,
    label: city.name.toUpperCase(),
  }))
  if (cityOptions.value.length === 0) {
    selectedCity.value = null // Reset city selection only if there are no cities
    studios.value = [] // Clear studios when there are no cities
  }
}

const handleCityChange = async (cityId: string) => {
  selectedCity.value = cityId
  localStorage.setItem("selectedCity", cityId.toString())
  updateURL()
  const studiosData = await getStudios(cityId)

  studios.value = studiosData.map((studio) => ({
    id: studio.id,
    logo: studio.company.logo_url,
    company: studio.company,
    name: studio.company.name,
    street: studio.street,
    photos: studio.photos,
    badges: studio.badges,
    prices: studio.prices,
    slug: studio.slug,
    operating_hours: studio.operating_hours,
    company_slug: studio.company.slug,
    price: studio.prices.length > 0 ? studio.prices[0].total_price : 0,
  }))

  //set unique badges

  if (filterShow.find((filter) => filter.key == "badges")) {
    filterShow.find((filter) => filter.key == "badges").options = studiosData
      .map((studio) => studio.badges)
      .flat()
      .filter(
        (badge, index, self) =>
          index === self.findIndex((b) => b.name === badge.name),
      )
      .map((badge) => ({
        id: badge.id,
        name: badge.name.charAt(0).toUpperCase() + badge.name.slice(1),
      }))
  }
}

const handleSearch = () => {
  localStorage.setItem("searchTerm", searchTerm.value)
  updateURL()
}

const updateURL = () => {
  const params = new URLSearchParams()
  if (selectedCountry.value) {
    params.append("country", selectedCountry.value.toString())
  }
  if (selectedCity.value) {
    params.append("city", selectedCity.value.toString())
  }
  if (searchTerm.value) {
    params.append("search", searchTerm.value)
  }
  window.history.replaceState(
    {},
    "",
    `${window.location.pathname}?${params.toString()}`,
  )
}

const loadFromLocalStorage = () => {
  const savedCountry = localStorage.getItem("selectedCountry")
  const savedCity = localStorage.getItem("selectedCity")
  const savedSearchTerm = localStorage.getItem("searchTerm")
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
  localStorage.removeItem("selectedCountry")
  localStorage.removeItem("selectedCity")
  localStorage.removeItem("searchTerm")
  selectedCountry.value = null
  selectedCity.value = null
  searchTerm.value = ""
  cityOptions.value = []
  studios.value = []
  updateURL()
}

const route = useRoute()
watch(
  route,
  (newRoute) => {
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
  },
  { immediate: true },
)
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
