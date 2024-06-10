<template>
  <div>
    <!-- Задний слой для блёра -->
    <div
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 transition-opacity duration-300 ease-in-out"
        :class="{ 'opacity-100': isOpen, 'opacity-0': !isOpen }"
        @click="closeMenu"
        v-show="isOpen"
    ></div>
    <!-- Само меню -->
    <div
        class="fixed inset-y-0 left-0 lg:w-64 w-full p-4 bg-black transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50"
        :class="{ 'translate-x-0': isOpen }"
    >
      <nav class="space-y-4">
        <a v-for="item in sideMenu" :key="item.name" href="#" class="block text-lg font-bold py-2 flex items-center">
          <img :src="item.path" alt="Icon" class="h-6 w-6 mr-2"/>
          {{ item.name }}
        </a>
        <button @click="closeMenu" class="text-white mt-4 flex items-center lg:hidden">
          <i class="fas fa-times mr-2"></i> Close menu
        </button>
      </nav>
    </div>
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

const closeMenu = () => {
  isOpen.value = false;
};

defineExpose({ toggleMenu, closeMenu });
</script>

<style scoped>
@media (min-width: 1024px) {
  .transform {
    transform: none !important;
  }
}
</style>
