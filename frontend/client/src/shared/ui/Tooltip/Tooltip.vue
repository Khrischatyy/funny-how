<template>
  <teleport to="body">
    <transition name="fade">
      <div v-show="tooltipData.isVisible" :class="{'animate__fadeIn': tooltipData.isVisible}" :style="tooltipStyle" class="tooltip animate__animated animate__faster">
        <slot></slot>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import {computed, inject} from 'vue';

const {tooltipData} = inject('tooltipData', {
  tooltipData: {
    isVisible: false,
    content: '',
    position: { x: 0, y: 0 }
  },
});

const tooltipStyle = computed(() => ({
  left: `${tooltipData.position.x}px`,
  top: `${tooltipData.position.y}px`
}));
</script>

<style lang="scss" scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity .3s cubic-bezier(.25,1,.5,1), visibility .3s cubic-bezier(.25,1,.5,1);
}
.fade-enter, .fade-leave-to {
  opacity: 0;
  visibility: hidden;
}
.fade-enter-to, .fade-leave {
  opacity: 1;
  visibility: visible;
}
.tooltip {
  position: absolute;
  z-index: 1000;
  background-color: black;
  color: white;
  padding: 8px;
  font-size: 12px;
  max-width: 180px;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  white-space: pre-wrap; // Handling \n
}
</style>