<template>
  <div
      class="text-white w-64 p-4 inset-y-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
      :class="{ 'translate-x-0': isOpen }"
  >
    <nav class="space-y-4">
      <a v-for="item in sideMenu" :key="item.name" href="#" class="block text-lg font-bold py-2 flex items-center">
        <img :src="item.path" alt="Icon" class="h-6 w-6 mr-2"/>
        {{ item.name }}
      </a>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineExpose } from 'vue';

const props = defineProps({
  sideMenu: {
    type: Array,
    required: true,
  },
});

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
