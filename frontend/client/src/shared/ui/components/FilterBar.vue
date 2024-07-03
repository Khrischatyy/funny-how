<template>
  <div class="flex flex-col lg:grid lg:grid-cols-4 lg:justify-between items-center gap-2 md:gap-2 mb-6 space-y-4 lg:space-y-0 w-full">
    <!-- Search and IconButton on the first line -->
    <div class="flex items-center w-full">
      <FInputClassic placeholder="Search" v-model="filters.search" class="w-full">
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
          <FSelectClassic v-if="getFilterShow('city')" placeholder="City" :options="getFilterShow('city')?.options" v-model="filters.city" class="w-full h-full">
            <template #icon>
              <IconStatus class="h-5 w-5 text-gray-400" />
            </template>
          </FSelectClassic>
          <FSelectClassic v-if="getFilterShow('status')" placeholder="Status" :options="[{id: 1, name: 'Status 1'}, {id: 2, name: 'Status 2'}]" v-model="filters.status" class="w-full h-full">
            <template #icon>
              <IconStatus class="h-5 w-5 text-gray-400" />
            </template>
          </FSelectClassic>

          <FInputClassic v-if="getFilterShow('date')" type="date" placeholder="Date" v-model="filters.date" size="sm" class="w-full h-full">
            <template #icon>
              <IconCalendar class="h-5 w-5 text-gray-400" />
            </template>
          </FInputClassic>



          <FInputClassic v-if="getFilterShow('price')" placeholder="Price up to" size="sm" v-model="filters.price" class="w-full h-full">
            <template #icon>
              <IconPrice class="h-5 w-5 text-gray-400" />
            </template>
          </FInputClassic>

        </div>
      </div>

      <!-- Rating, Filter, Clear Filter on the third line -->
      <div class="flex w-full justify-end items-center gap-6">
        <FInputClassic v-if="getFilterShow('time')" type="time" placeholder="Time" v-model="filters.time" size="sm" class="w-full h-full">
          <template #icon>
            <IconClock class="h-5 w-5 text-gray-400" />
          </template>
        </FInputClassic>
        <FInputClassic v-if="getFilterShow('rating')" placeholder="Rating from" size="sm" v-model="filters.rating" class="w-full h-full">
          <template #icon>
            <IconRating class="h-5 w-5 text-gray-400" />
          </template>
        </FInputClassic>
      </div>
      <div class="flex w-full justify-between items-center gap-2">
        <button class="bg-white text-black p-2 rounded-[10px]  w-full h-full flex items-center justify-center">
          <i class="fas fa-filter mr-1"></i> Filter
        </button>
        <button @click="clearFilters" class="border border-red-500 p-2 rounded-[10px]  w-full h-full flex items-center justify-center text-red-500">
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
import {FInput, FInputClassic, FSelectClassic, IconCalendar, IconClock} from "~/src/shared/ui/common";
import {computed, onMounted, ref, watch} from "vue";
import {debounce} from "@antfu/utils";

const emits = defineEmits(['update:filters']);
const props = withDefaults(defineProps<{
  filtersShow: any;
}>(), {
  filtersShow: [
    {key: 'search', options:''},
    {key: 'status', options: [{id: 1, name: 'Status 1'}, {id: 2, name: 'Status 2'}]},
    {key: 'price', options:[{id: 1, name: 'Price 1'}, {id: 2, name: 'Price 2'}]},
    {key: 'rating', options:[{id: 1, name: 'Rating 1'}, {id: 2, name: 'Rating 2'}]},
  ]
});

const filters = ref({
  search: '',
  status: null,
  price: null,
  rating: null,
  date: null,
  time: null,
  city: null,
})
const dateInput = ref(null)
const isFiltersOpen = ref(false)
const openFiltersToogle = () => isFiltersOpen.value = !isFiltersOpen.value

const checkDesktop = () => {
  return process.client && window.innerWidth > 1024;
}
const getFilterShow = (key) => {
  return props.filtersShow.find((filter) => filter.key == key)
}

const debouncedUpdateFilters = debounce(700, () => {
  emits('update:filters', filters.value);
});

watch(filters, debouncedUpdateFilters, { deep: true });

const clearFilters = () => {
  filters.value = {
    search: '',
    status: null,
    price: null,
    rating: null,
    date: null,
    time: null,
  }
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
