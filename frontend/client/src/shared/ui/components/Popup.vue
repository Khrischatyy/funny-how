<script setup lang="ts">
import {defineEmits, defineProps, onBeforeUnmount, onMounted, onUnmounted, withDefaults} from 'vue';
import {IconClose} from "~/src/shared/ui/common";

const emit = defineEmits(['close']);

const closePopup = () => {
  emit('close');
}

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closePopup();
  }
}

onMounted(() => {
  document.body.style.overflow = 'hidden';
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.body.style.overflow = 'auto';
  window.removeEventListener('keydown', handleKeyDown);
});

export type PopupType = 'default' | 'success' | 'error' | 'warning' | 'small';

const props = withDefaults(defineProps<{
  title?: string,
  open: boolean,
  type?: PopupType
}>(), {
  title: '',
  open: false,
  type: 'default'
});
</script>

<template>
  <div v-if="open" class="modal backdrop-blur-[10px] overflow-hidden fixed inset-0 flex items-center justify-center z-20 p-0 sm:p-10 overflow-y-auto z-50">
    <div :class="{'max-w-lg mx-auto': props.type == 'small'}" class="modal-content flex flex-col gap-5 bg-[#171717] rounded-[10px] shadow-lg w-full p-6 relative z-20">
      <div class="modal-header flex justify-between items-center mb-4">
        <slot name="header" class="text-white text-[22px]/[26px] font-bold" />
        <slot name="action_header">
          <div @click="closePopup" class="flex flex-col justify-center items-center cursor-pointer">
            <IconClose />
            <span class="opacity-20">Close</span>
          </div>
        </slot>
      </div>
      <div class="modal-body mb-10">
        <slot name="body" />
      </div>
      <div class="modal-footer w-full">
        <slot name="footer" />
      </div>
    </div>
    <div @click="closePopup" class="fixed inset-0 bg-black bg-opacity-50 z-10"></div>
  </div>
</template>

<style scoped lang="scss">
.modal-content {
  width: 100%;
  margin: auto;
}
</style>