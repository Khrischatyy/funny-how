<template>
  <div class="relative flex items-center w-full" ref="scrollContainer">
    <button
        v-if="checkScrollVisibility"
        @click.stop="scrollLeft"
        :class="theme === 'dark' ? 'from-[#000]' : `from-${mainColor}`"
        class="cursor-pointer w-auto rounded-tr-[10px] rounded-tb-[10px] backdrop-blur-[1px] h-full bg-gradient-to-r to-transparent rounded-lg absolute flex items-center justify-start left-0 border-none p-0 z-10"
    >
      <IconLeft iconType="thin" />
    </button>
    <div
        v-bind="$attrs"
        ref="scrollableContainer"
        class="relative flex justify-base w-full h-auto overflow-x-auto overflow-y-visible scroll-smooth no-scrollbar gap-5"
        @scroll="checkButtons"
    >
      <!-- Slot for template items -->
      <slot></slot>
    </div>
    <button
        v-if="checkScrollVisibility"
        @click.stop="scrollRight"
        :class="theme === 'dark' ? 'from-[#000]' : `from-${mainColor}`"
        class="cursor-pointer w-auto rounded-tl-[10px] rounded-bl-[10px] backdrop-blur-[1px] h-full bg-gradient-to-l to-transparent rounded-lg absolute flex items-center justify-end right-0 border-none p-0 z-10"
    >
      <IconRight iconType="thin" />
    </button>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted, watch, reactive, provide} from 'vue';
import {IconLeft, IconRight} from "~/src/shared/ui/common";
import {Tooltip} from "~/src/shared/ui/Tooltip";
const props = withDefaults(defineProps<{
  theme?: string,
  mainColor?: string
}>(), {
  theme: 'default',
  mainColor: 'black'
});
const scrollContainer = ref(null);
const scrollableContainer = ref(null);

const checkScrollVisibility = ref(false);

// Check visibility of left/right scroll buttons
const checkButtons = () => {
  if (scrollableContainer.value) {
    checkScrollVisibility.value =
        scrollableContainer.value.scrollWidth > scrollableContainer.value.clientWidth;
  }
};

// Methods to scroll left and right
const scrollLeft = () => {
  scrollableContainer.value.scrollBy({ left: -300, behavior: 'smooth' });
};

const scrollRight = () => {
  scrollableContainer.value.scrollBy({ left: 300, behavior: 'smooth' });
};

onMounted(() => {
  checkButtons(); // Initial check for button visibility
  window.addEventListener('resize', checkButtons); // Recheck on window resize
});

watch(scrollableContainer, () => {
  checkButtons();
});
</script>

<style>
.justify-base {
  justify-content: space-between;
}
.justify-center-important {
  justify-content: center !important;
}
.justify-start-important {
  justify-content: start !important;
}
</style>

<style scoped lang="scss">

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
:deep(.scrollElement){
  flex-shrink: 0;
  &:first-of-type {
    margin-left: 25px;
  }
  &:last-of-type {
    margin-right: 25px;
  }
}
</style>