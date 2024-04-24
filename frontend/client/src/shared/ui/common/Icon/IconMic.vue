<template>
  <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="5.54932" y="2.57422" width="9.90074" height="13.201" rx="3.9603" :stroke="iconColor" stroke-linejoin="round"/>
    <path d="M10.5 18.2506V15.7754" :stroke="iconColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M8.8497 10.8262H6.37451" :stroke="iconColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15.4504 10.8262H13.8003" :stroke="iconColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M8.8497 7.52539H6.37451" :stroke="iconColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15.4504 7.52539H13.8003" :stroke="iconColor" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>
<script setup lang="ts">
const props = withDefaults(defineProps<{
  iconColor?: string,
}>(), {
  iconColor: '#ffffff',
})
</script>