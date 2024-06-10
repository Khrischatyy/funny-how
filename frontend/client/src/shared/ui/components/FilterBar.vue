<template>
  <div class="flex flex-col lg:flex-row items-center lg:space-x-4 mb-6 space-y-4 lg:space-y-0 w-full">
    <div class="relative flex-1 lg:flex-none">
      <IconSearch class="absolute top-1/2 transform -translate-y-1/2 left-3 h-5 w-5 text-gray-400" />
      <input type="text" placeholder="Search" class="bg-black text-white p-2 pl-10 rounded-md w-full" />
    </div>
    <div class="relative flex-1 lg:flex-none">
      <IconStatus class="absolute top-1/2 transform -translate-y-1/2 left-3 h-5 w-5 text-gray-400" />
      <select class="bg-black text-white p-2 pl-10 rounded-md w-full">
        <option>Status</option>
        <!-- Options here -->
      </select>
    </div>
    <div class="relative flex-1 lg:flex-none">
      <IconPrice class="absolute top-1/2 transform -translate-y-1/2 left-3 h-5 w-5 text-gray-400" />
      <select class="bg-black text-white p-2 pl-10 rounded-md w-full">
        <option>Price</option>
        <!-- Options here -->
      </select>
    </div>
    <div class="relative flex-1 lg:flex-none">
      <IconRating class="absolute top-1/2 transform -translate-y-1/2 left-3 h-5 w-5 text-gray-400" />
      <select class="bg-black text-white p-2 pl-10 rounded-md w-full">
        <option>Rating</option>
        <!-- Options here -->
      </select>
    </div>
    <div class="flex flex-col lg:flex-row items-center lg:space-x-2 space-y-2 lg:space-y-0 flex-1 lg:flex-none">
      <button class="bg-white text-black p-2 rounded-md flex-1 lg:flex-none flex items-center justify-center">
        <i class="fas fa-filter mr-1"></i> Filter
      </button>
      <button class="border border-red-500 p-2 rounded-md flex-1 lg:flex-none flex items-center justify-center text-red-500">
        <i class="fas fa-times mr-1"></i> Clear filter
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import IconPrice from '~/src/shared/ui/common/Icon/Filter/IconPrice.vue';
import IconRating from '~/src/shared/ui/common/Icon/Filter/IconRating.vue';
import IconSearch from '~/src/shared/ui/common/Icon/Filter/IconSearch.vue';
import IconStatus from '~/src/shared/ui/common/Icon/Filter/IconStatus.vue';
</script>

<style scoped>
input, select, button {
  border-radius: 0.375rem; /* Закругленные края */
}
</style>
