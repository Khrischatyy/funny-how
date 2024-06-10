<template>
  <div class="bg-black p-4 rounded-md shadow-lg flex flex-col justify-between">
    <div class="flex justify-between items-start mb-4">
      <img :src="studio.logo" alt="Logo" class="h-12 w-12 object-cover rounded-full" />
      <button class="text-red-500">
        <i class="fas fa-trash-alt"></i>
      </button>
    </div>
    <div>
      <h3 class="text-xl font-bold">{{ studio.name }}</h3>
      <p class="text-gray-400">{{ studio.address }}</p>
    </div>
    <div class="mt-4 flex justify-between items-center">
      <div class="flex items-center">
        <i class="fas fa-clock text-gray-400 mr-1"></i>
        <span class="text-gray-400">{{ studio.hours }}</span>
      </div>
      <div class="flex items-center">
        <i class="fas fa-dollar-sign text-gray-400 mr-1"></i>
        <span class="text-gray-400">{{ studio.price }}$ / hour</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  studio: {
    type: Object,
    required: true
  }
});
</script>

<style scoped>
.card {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.card img {
  border-radius: 50%;
}
.card h3 {
  font-size: 1.25rem;
  font-weight: bold;
}
.card p {
  color: #9ca3af;
}
.card .details {
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card .details i {
  color: #9ca3af;
}
.card .details span {
  color: #9ca3af;
}
</style>
