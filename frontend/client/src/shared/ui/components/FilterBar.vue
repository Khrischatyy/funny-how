<template>
  <div class="flex flex-col lg:grid lg:grid-cols-3 lg:justify-between items-center gap-2 md:gap-6 mb-6 space-y-4 lg:space-y-0 w-full">
    <!-- Search and IconButton on the first line -->
    <div class="flex items-center w-full">
      <FInputClassic label="Search" v-model="filters.search" class="w-full">
        <template #icon>
          <IconSearch class="h-5 w-5 text-gray-400" />
        </template>
      </FInputClassic>
      <IconButton @click="openFiltersToogle" :isActive="isFiltersOpen" class="ml-2 lg:hidden open-filters"/>
    </div>

    <template v-if="isFiltersOpen">
      <!-- Status and Price on the second line -->
      <div class="flex items-center w-full flex-col lg:flex-row gap-6">

        <div class="flex w-full h-full gap-2">
          <FSelectClassic label="Status" :options="[{id: 1, name: 'Status 1'}, {id: 2, name: 'Status 2'}]" v-model="filters.status" class="w-full h-full">
            <template #icon>
              <IconStatus class="h-5 w-5 text-gray-400" />
            </template>
          </FSelectClassic>

          <FSelectClassic label="Price" :options="[{id: 1, name: 'Price 1'}, {id: 2, name: 'Price 2'}]" v-model="filters.price" class="w-full h-full">
            <template #icon>
              <IconPrice class="h-5 w-5 text-gray-400" />
            </template>
          </FSelectClassic>

        </div>
      </div>

      <!-- Rating, Filter, Clear Filter on the third line -->
      <div class="flex w-full justify-center items-center gap-6">
        <FSelectClassic label="Rating" :options="[{id: 1, name: 'Rating 1'}, {id: 2, name: 'Rating 2'}]" v-model="filters.rating" class="w-full h-full">
          <template #icon>
            <IconRating class="h-5 w-5 text-gray-400" />
          </template>
        </FSelectClassic>

        <button class="bg-white text-black p-2 rounded-md w-full h-full flex items-center justify-center">
          <i class="fas fa-filter mr-1"></i> Filter
        </button>
        <button class="border border-red-500 p-2 rounded-md w-full h-full flex items-center justify-center text-red-500">
          <i class="fas fa-times mr-1"></i> Clear filter
        </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import IconPrice from '~/src/shared/ui/common/Icon/Filter/IconPrice.vue';
import IconRating from '~/src/shared/ui/common/Icon/Filter/IconRating.vue';
import IconSearch from '~/src/shared/ui/common/Icon/Filter/IconSearch.vue';
import IconStatus from '~/src/shared/ui/common/Icon/Filter/IconStatus.vue';
import IconButton from '~/src/shared/ui/common/Icon/Filter/IconButton.vue';
import {FInput, FInputClassic, FSelectClassic} from "~/src/shared/ui/common";
import {computed, onMounted, ref} from "vue";

const filters = ref({
  search: '',
  status: null,
  price: null,
  rating: null
})
const isFiltersOpen = ref(false)
const openFiltersToogle = () => isFiltersOpen.value = !isFiltersOpen.value

const checkDesktop = () => {
  return process.client && window.innerWidth > 1024;
}

onMounted(() => {
  isFiltersOpen.value = checkDesktop() as boolean

  window.addEventListener('resize', () => {
    isFiltersOpen.value = checkDesktop() as boolean
  })
})

</script>

<style scoped>
input, select, button {
  border-radius: 0.375rem; /* Закругленные края */
}
</style>
