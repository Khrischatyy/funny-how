<template>
  <div
    :class="`border-${borderColor} border-opacity-${borderOpacity}`"
    class="p-[10px] text-white rounded-[10px] h-full flex items-center justify-center border border-dashed cursor-pointer"
  >
    <div class="flex flex-col justify-center items-center m-10">
      <IconAddRing />
      <span class="text-2xl">{{ props.title }}</span>
      <span class="text-sm text-neutral-700">{{ props.subtitle }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
// No additional logic needed for now
import { IconAddRing } from "~/src/shared/ui/common"
const props = withDefaults(
  defineProps<{
    title?: string
    subtitle?: string
    borderColor?: string
    borderOpacity?: string
  }>(),
  {
    title: "Add Studio",
    borderColor: "white",
    borderOpacity: "20",
  },
)
</script>

<style scoped>
.border-dashed {
  border-width: 2px;
  border-style: dashed;
}
.m-10 {
  margin: 10px;
}
</style>
