<template>
  <div class="flex flex-col lg:flex-row items-center lg:space-x-4 mb-6 space-y-4 lg:space-y-0 w-full">
    <input type="text" placeholder="Search" class="bg-black text-white p-2 rounded-md flex-1" />
    <select class="bg-black text-white p-2 rounded-md flex-1 lg:flex-none">
      <option>Status</option>
      <!-- Options here -->
    </select>
    <select class="bg-black text-white p-2 rounded-md flex-1 lg:flex-none">
      <option>Price</option>
      <!-- Options here -->
    </select>
    <select class="bg-black text-white p-2 rounded-md flex-1 lg:flex-none">
      <option>Rating</option>
      <!-- Options here -->
    </select>
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
// No additional logic needed for now
</script>

<style scoped>
input, select, button {
  border-radius: 0.375rem; /* Закругленные края */
}
</style>
