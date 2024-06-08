<template>
  <div class="flex flex-col lg:flex-row items-center lg:space-x-4 mb-6 space-y-4 lg:space-y-0 w-full">
    <SearchBar class="flex-1" />
    <select class="bg-black text-white p-2 rounded w-full lg:w-auto">
      <option>Status</option>
      <!-- Options here -->
    </select>
    <select class="bg-black text-white p-2 rounded w-full lg:w-auto">
      <option>Price</option>
      <!-- Options here -->
    </select>
    <select class="bg-black text-white p-2 rounded w-full lg:w-auto">
      <option>Rating</option>
      <!-- Options here -->
    </select>
    <div class="flex flex-col lg:flex-row items-center lg:space-x-2 space-y-2 lg:space-y-0 w-full lg:w-auto">
      <button class="bg-white text-black p-2 rounded flex items-center w-full lg:w-auto">
        <i class="fas fa-filter mr-1"></i> Filter
      </button>
      <button class="border border-red-500 p-2 rounded flex items-center text-red-500 w-full lg:w-auto">
        <i class="fas fa-times mr-1"></i> Clear filter
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SearchBar } from '~/src/shared/ui/components';
</script>
