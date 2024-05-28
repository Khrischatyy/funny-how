<template>
  <div class="flex items-center w-full">
    <span class="absolute left-5 top-0 text-neutral-700 cursor-pointer">{{ label }}</span>

    <TimePicker :available-hours="availableHours" :renting-form="rentingForm" :open="open" @dragging="open = !open" v-if="rentingForm" class="w-full border-double focus:border-white border border-opacity-20 bg-transparent text-white text-sm font-medium tracking-wide" time="06:00" @timeChange="timeChanged($event, 'start_time')" />
    <div class="border-l border-white h-full w-[1px] rotate-90"></div>
    <div @click="open = !open" class="absolute w-9 pl-1 border-white right-0 top-[45px] cursor-pointer">
      <IconDown :class="open ? 'rotate-180' : 'rotate-0'" :opacity="1"/>
    </div>
  </div>
</template>
<script setup lang="ts">
import {TimePicker} from "~/src/features/DatePicker";
import {IconDown} from "~/src/shared/ui/common";
import {defineProps, defineEmits, ref} from 'vue'

const props = defineProps<{
  rentingForm: any,
  label: string,
  time: {
    type: String,
    required: true,
    default: '09:00'
  },
   availableHours: {
    type: Array,
    required: false,
    default: []
  }
}>()

const open = ref(false)
const emit = defineEmits(['timeChanged'])

const timeChanged = (time: string, key: string) => {
  emit('timeChanged', time, key)
}

</script>

<style scoped>
.border-double{
  min-height: 61px;
}
.border-double::before{
  content: '';
  position: absolute;
  bottom: 32px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}
.border-double::after{
  content: '';
  position: absolute;
  bottom: 34px;
  left: 0;
  width: 100%;
  height: 100%;
  border-bottom: 1px solid white;
  pointer-events: none;
  z-index: 1;
}
</style>