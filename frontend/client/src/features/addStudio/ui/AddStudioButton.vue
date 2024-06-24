<template>
  <div class="text-white rounded-[10px] h-full flex items-center justify-center border border-dashed border-white border-opacity-20 cursor-pointer">
    <div class="flex flex-col justify-center items-center gap-5">
      <IconAddRing/>
      <span class="text-2xl">{{ props.title }}</span>
      <span class="text-sm text-neutral-700">{{ props.subtitle }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
// No additional logic needed for now
import {IconAddRing} from "~/src/shared/ui/common";
const props = withDefaults(defineProps<{
  title: string,
  subtitle?: string,
}>(), {
  title: 'Add Studio',
});
</script>

<style scoped>
.border-dashed {
  border-width: 2px;
  border-style: dashed;
}
</style>