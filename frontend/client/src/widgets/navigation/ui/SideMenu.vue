<template>
  <div
      class="text-white w-64 p-4 inset-y-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out "
      :class="{ 'translate-x-0': isOpen }"
  >
    <nav class="space-y-4">
      <a href="#" class="block text-lg font-bold py-2">Studios</a>
      <a href="#" class="block text-lg font-bold py-2">History</a>
      <a href="#" class="block text-lg font-bold py-2">Booking management</a>
      <a href="#" class="block text-lg font-bold py-2">Profile</a>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

defineExpose({ toggleMenu });
</script>

<style scoped>
@media (min-width: 1024px) {
  .transform {
    transform: none !important;
  }
}
</style>
