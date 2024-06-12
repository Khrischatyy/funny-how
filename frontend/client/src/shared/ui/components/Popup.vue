<script setup lang="ts">
import { defineEmits, defineProps, withDefaults } from 'vue';
import {IconClose} from "~/src/shared/ui/common";

const emit = defineEmits(['close']);

const closePopup = () => {
  emit('close');
}

const props = withDefaults(defineProps<{
  title?: string,
  open: boolean
}>(), {
  title: '',
  open: false
});
</script>

<template>
  <div v-if="open" class="modal backdrop-blur-[10px] fixed inset-0 flex items-center justify-center z-20 p-10">
    <div class="modal-content bg-[#171717] rounded-[10px] shadow-lg w-full max-w-lg mx-auto p-6 relative z-20">
      <div class="modal-header flex justify-between items-center mb-4">
        <slot name="header" class="text-white text-[22px]/[26px] font-bold" />
        <div @click="closePopup" class="flex flex-col justify-center items-center cursor-pointer">
          <IconClose />
          <span class="opacity-20">Close</span>
        </div>
      </div>
      <div class="modal-body">
        <slot name="body" />
      </div>
    </div>
    <div @click="closePopup" class="fixed inset-0 bg-black bg-opacity-50 z-10"></div>
  </div>
</template>

<style scoped lang="scss">
.modal-content {
  max-width: 500px;
  width: 100%;
  margin: auto;
}
</style>