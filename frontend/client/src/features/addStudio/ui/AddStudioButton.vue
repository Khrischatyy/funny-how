<template>
  <div class="text-white rounded-[10px] flex items-center justify-center border border-dashed border-white border-opacity-20 cursor-pointer">
    <div class="flex flex-col justify-center items-center gap-5">
      <IconAddRing/>
      <span class="text-2xl">Add Studio</span>
    </div>
  </div>
</template>

<script setup lang="ts">
// No additional logic needed for now
import {IconAddRing} from "~/src/shared/ui/common";
</script>

<style scoped>
.border-dashed {
  border-width: 2px;
  border-style: dashed;
}
</style>