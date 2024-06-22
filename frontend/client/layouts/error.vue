<script setup lang="ts">

import {Particles} from "~/src/shared/ui/components";
</script>

<template>
  <div class="flex justify-center items-center text-white text-xxl h-full min-h-[100vh]">
    <slot />
    <Particles />
  </div>
</template>

<style scoped lang="scss">

</style>