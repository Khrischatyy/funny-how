<template>
  <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M6.25 8.75H23.75" stroke="white" stroke-width="2" stroke-linecap="round"/>
    <path d="M6.25 15H23.75" stroke="white" stroke-width="2" stroke-linecap="round"/>
    <path d="M6.25 21.25H23.75" stroke="white" stroke-width="2" stroke-linecap="round"/>
  </svg>
</template>
<script setup lang="ts">
const props = withDefaults(defineProps<{
  iconColor?: string,
}>(), {
  iconColor: '#ffffff',
})
</script>